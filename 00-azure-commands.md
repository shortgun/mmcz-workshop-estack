## Network operations

```
azure network vnet subnet create MMCZWS MMVnet MMVnetSubnet -a 10.10.80.0/24
azure network public-ip create -g MMCZWS -n MMCZPublicLB -l westeurope -d mmczworkshop -a static -i 4
```
## load balancer

```
azure network lb create  MMCZWS MMCZWSLB westeurope
azure network lb frontend-ip create MMCZWS MMCZWSLB frontendpool -i MMCZPublicLB
azure network lb address-pool create MMCZWS MMCZWSLB backendpool
azure network lb rule create MMCZWS MMCZWSLB httpbrule -p tcp -f 80 -b 80 -t frontendpool -o backendpool
azure network lb probe create -g MMCZWS -l MMCZWSLB -n httphealthprobe -p "http" -o 80 -f ping.php -i 30 -c 2
azure network lb show MMCZWS MMCZWSLB

## NAT Rules
```
azure network lb inbound-nat-rule create -g MMCZWS -l MMCZWSLB -n ssh01 -p tcp -f 21 -b 22
azure network lb inbound-nat-rule create -g MMCZWS -l MMCZWSLB -n ssh02 -p tcp -f 23 -b 22
```
## Public IP
```
azure network public-ip create -g MMCZWS -n MMCZPublicLB -l westeurope -d mmczworkshop -a static -i 4

azure network nic create -g MMCZWS -n lb-nic01-be --subnet-name MMVnetSubnet --subnet-vnet-name MMVnet -o mmczwssg -d "/subscriptions/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx/resourceGroups/MMCZWS/providers/Microsoft.Network/loadBalancers/MMCZWSLB/backendAddressPools/backendpool" -e "/subscriptions/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx/resourceGroups/MMCZWS/providers/Microsoft.Network/loadBalancers/MMCZWSLB/inboundNatRules/ssh01" westeurope

azure network nic create -g MMCZWS -n lb-nic02-be --subnet-name MMVnetSubnet --subnet-vnet-name MMVnet -o mmczwssg -d "/subscriptions/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx/resourceGroups/MMCZWS/providers/Microsoft.Network/loadBalancers/MMCZWSLB/backendAddressPools/backendpool" -e "/subscriptions/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx/resourceGroups/MMCZWS/providers/Microsoft.Network/loadBalancers/MMCZWSLB/inboundNatRules/ssh02" westeurope
```
## Bind Existent NICs

```
azure network nic set -g mmczws -n magento02175 -d "/subscriptions/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx/resourceGroups/MMCZWS/providers/Microsoft.Network/loadBalancers/MMCZWSLB/backendAddressPools/backendpool"
azure network nic set -g mmczws -n magento0110 -d "/subscriptions/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx/resourceGroups/MMCZWS/providers/Microsoft.Network/loadBalancers/MMCZWSLB/backendAddressPools/backendpool"
```



### Output Example for create command

```
aklochkov@MacBook-Pro-Andrew:~$ azure network lb frontend-ip create MMCZWS MMCZWSLB frontendpool -i MMCZPublicLB
info:    Executing command network lb frontend-ip create
+ Looking up the load balancer "MMCZWSLB"                                      
+ Looking up the public ip "MMCZPublicLB"                                      
+ Updating load balancer "MMCZWSLB"                                            
data:    Name                            : frontendpool
data:    Provisioning state              : Succeeded
data:    Private IP allocation method    : Dynamic
data:    Public IP address id            : /subscriptions/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx/resourceGroups/MMCZWS/providers/Microsoft.Network/publicIPAddresses/MMCZPublicLB
info:    network lb frontend-ip create command OK
```

### Verify Balancer

```
aklochkov@MacBook-Pro-Andrew:~$ azure network lb show MMCZWS MMCZWSLB
info:    Executing command network lb show
+ Looking up the load balancer "MMCZWSLB"                                      
data:    Id                              : /subscriptions/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx/resourceGroups/MMCZWS/providers/Microsoft.Network/loadBalancers/MMCZWSLB
data:    Name                            : MMCZWSLB
data:    Type                            : Microsoft.Network/loadBalancers
data:    Location                        : westeurope
data:    Provisioning state              : Succeeded
data:    
data:    Frontend IP configurations:
data:    Name          Provisioning state  Private IP allocation  Private IP   Subnet  Public IP   
data:    ------------  ------------------  ---------------------  -----------  ------  ------------
data:    frontendpool  Succeeded           Dynamic                                     MMCZPublicLB
data:    
data:    Probes:
data:    Name             Provisioning state  Protocol  Port  Path      Interval  Count
data:    ---------------  ------------------  --------  ----  --------  --------  -----
data:    httphealthprobe  Succeeded           Http      80    ping.php  30        2    
data:    
data:    Backend Address Pools:
data:    Name         Provisioning state
data:    -----------  ------------------
data:    backendpool  Succeeded         
data:    
data:    Load Balancing Rules:
data:    Name       Provisioning state  Load distribution  Protocol  Frontend port  Backend port  Enable floating IP  Idle timeout in minutes
data:    ---------  ------------------  -----------------  --------  -------------  ------------  ------------------  -----------------------
data:    httpbrule  Succeeded           Default            Tcp       80             80            false               4                      
info:    network lb show command OK

```

### Interfaces Listing

```
aklochkov@MacBook-Pro-Andrew:~$ azure network nic list
info:    Executing command network nic list
+ Getting the network interfaces                                               
data:    Name          Location    Resource group  Provisioning state  MAC Address        IP forwarding  Internal DNS name  Internal FQDN
data:    ------------  ----------  --------------  ------------------  -----------------  -------------  -----------------  -------------
data:    es01974       westeurope  MMCZWS          Succeeded           00-0D-3A-21-04-E2  false                                          
data:    es02547       westeurope  MMCZWS          Succeeded           00-0D-3A-21-E1-8D  false                                          
data:    kibana320     westeurope  MMCZWS          Succeeded           00-0D-3A-24-08-30  false                                          
data:    magento0191   westeurope  MMCZWS          Succeeded           00-0D-3A-20-6D-DD  false                                          
data:    magento02209  westeurope  MMCZWS          Succeeded           00-0D-3A-20-88-17  false                                          
data:    magentodb180  westeurope  MMCZWS          Succeeded           00-0D-3A-20-A1-BA  false                                          
info:    network nic list command OK
```