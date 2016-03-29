# Whole configs
## IP Addressing

| Node      | IntIP         | ExtIP                          |
|-----------|---------------|--------------------------------|
| magento01 | 10.10.80.4    | 40.68.39.47                    |
| magento02 | 10.10.80.5    | 40.68.37.101                   |
| magentodb | 10.10.80.6    | 13.94.198.179                  |
| es01      | 10.10.80.7    | 40.118.110.146                 |
| es02      | 10.10.80.8    | 40.118.101.155                 |
| kibana    | 10.10.80.9    | 40.68.152.151                  |
| redis     | 40.68.153.145 | mmczws.redis.cache.windows.net |


info:    Executing command vm list
+ Getting virtual machines                                                     
data:    ResourceGroupName  Name       ProvisioningState  PowerState  Location    Size          
data:    -----------------  ---------  -----------------  ----------  ----------  --------------
data:    MMCZWS             es01       Succeeded          VM running  westeurope  Standard_D1_v2
data:    MMCZWS             es02       Succeeded          VM running  westeurope  Standard_D1_v2
data:    MMCZWS             kibana     Succeeded          VM running  westeurope  Standard_A1   
data:    MMCZWS             magento01  Succeeded          VM running  westeurope  Standard_A1   
data:    MMCZWS             magento02  Succeeded          VM running  westeurope  Standard_A1   
data:    MMCZWS             magentodb  Succeeded          VM running  westeurope  Standard_A1   
