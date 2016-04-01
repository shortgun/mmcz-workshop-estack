# Whole configs
## IP Addressing

| Node      | IntIP         | ExtIP                          |
|-----------|---------------|--------------------------------|
| magento01 | 10.10.80.4    | 40.68.224.106                    |
| magento02 | 10.10.80.5    | 40.68.228.85                   |
| magentodb | 10.10.80.6    | 13.94.198.179                  |
| es01      | 10.10.80.7    | 40.118.110.146                 |
| es02      | 10.10.80.8    | 40.118.101.155                 |
| kibana    | 10.10.80.9    | 40.68.152.151                  |
| redis     | 40.68.153.145 | mmczws.redis.cache.windows.net |


```
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
```
## Commands
### All servers
```
setenforce 0
cat <<EOF > /etc/selinux/config
# This file controls the state of SELinux on the system.
# SELINUX= can take one of these three values:
#     enforcing - SELinux security policy is enforced.
#     permissive - SELinux prints warnings instead of enforcing.
#     disabled - No SELinux policy is loaded.
SELINUX=enforcing
# SELINUXTYPE= can take one of these two values:
#     targeted - Targeted processes are protected,
#     mls - Multi Level Security protection.
SELINUXTYPE=disabled 

```

### Db Server

```
mkdir -p /srv/exports
yum -y install nfs-utils
cat <<EOF >/etc/exports
/srv/exports 10.10.80.0/24(rw,no_root_squash,no_subtree_check)
EOF
esportfs -ra
```

### Client Nodes Install
```
yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-6.noarch.rpm
yum -y install http://rpms.remirepo.net/enterprise/remi-release-6.rpm
yum -y install yum-utils
yum-config-manager --enable remi-php56
yum update
yum -y install php php-pecl-imagick php-gd php-mcrypt php-mbstring php-intl php-soap php-xml php-xmlrpc php-pecl-zip php-pdo php-mysqlnd php-pecl-redis
php --version
php --modules

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```
```
vim /etc/php.ini
always_populate_raw_post_data = -1
max_execution_time = 180
memory_limit = 512M
```
```
php bin/magento setup:static-content:deploy
```
