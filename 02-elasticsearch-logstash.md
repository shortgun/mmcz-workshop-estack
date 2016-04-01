# GeoIP installation for Logstash

Logstash and Beats can't use standard GeoIP database. GeoLiteCity database required for normal work. Finally standard GeoIP engine and function calls will be used together with GeoLiteCity. Run commands above to prepare LS/ES environment for better geo locating.

```
rpm -ivh http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
yum -y install GeoIP
curl http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz -o /usr/share/GeoIP/GeoLiteCity.dat.gz && gunzip /usr/share/GeoIP/GeoLiteCity.dat.gz
```

# Elasticsearch
## Installation
### APT (deb)
```
wget -qO - https://packages.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
echo "deb http://packages.elastic.co/elasticsearch/2.3/debian stable main" | sudo tee -a /etc/apt/sources.list.d/elasticsearch-2.3.list
sudo apt-get update && sudo apt-get install elasticsearch
```

Configure Elasticsearch to automatically start during bootup. If your distribution is using SysV init, then you will need to run:

```
sudo update-rc.d elasticsearch defaults 95 10
```
Otherwise if your distribution is using systemd:

```
sudo /bin/systemctl daemon-reload
sudo /bin/systemctl enable elasticsearch.service
```

### YUM (rpm)
Add GPG-KEY first

```
rpm --import https://packages.elastic.co/GPG-KEY-elasticsearch
```
Add rpm repo file in a next step

```
cat <<EOF >/etc/yum.repos.d/elasticsearch.repo
[elasticsearch-2.x]
name=Elasticsearch repository for 2.x packages
baseurl=https://packages.elastic.co/elasticsearch/2.x/centos
gpgcheck=1
gpgkey=https://packages.elastic.co/GPG-KEY-elasticsearch
enabled=1
EOF
```
And just run yum for package install:

```
yum -y install elasticsearch
```

Configure Elasticsearch to automatically start during bootup. If your distribution is using SysV init (check with ps -p 1), then you will need to run:

```
chkconfig --add elasticsearch
```
Otherwise if your distribution is using systemd:

```
sudo /bin/systemctl daemon-reload
sudo /bin/systemctl enable elasticsearch.service
```

## Configuration
Really easy!

```
cat <<EOF >/etc/elasticsearch/elasticsearch.yml
cluster.name: beats
node.name: es1
#path.data: /srv/elasticsearch/data
#path.logs: /srv/elasticsearch/log
network.host: _eth0_
http.port: 9200
discovery.zen.ping.unicast.hosts: ["10.10.80.7", "10.10.80.8"]
#
# Disable starting multiple nodes on a single system:
#
node.max_local_storage_nodes: 1
EOF

```


# Logstash
## Installation
### YUM (rpm)
Add GPG-KEY first

```
rpm --import https://packages.elastic.co/GPG-KEY-elasticsearch
```
Add rpm repo file in a next step

```
cat <<EOF >/etc/yum.repos.d/logstash.repo
[logstash-2.2]
name=Logstash repository for 2.2.x packages
baseurl=http://packages.elastic.co/logstash/2.2/centos
gpgcheck=1
gpgkey=http://packages.elastic.co/GPG-KEY-elasticsearch
enabled=1
EOF
```
And just run yum for package install:

```
yum -y install logstash
```

Configure logstash to automatically start during bootup. If your distribution is using SysV init (check with ps -p 1), then you will need to run:

```
chkconfig --add logstash
```
Otherwise if your distribution is using systemd:

```
sudo /bin/systemctl daemon-reload
sudo /bin/systemctl enable logstash.service
```

## Configuration

Prepare file structure for configuration files

```
mkdir -p /etc/logstash/conf.d /etc/logstash/extra_patterns /etc/logstash/patterns
```

Now we need some configuration files. Lofstash logic is simple. All configuration commands and settings going to live in "from top to bottom" order. Configuration usually placed inside /etc/logstash/conf.d folder. First we need to configure inputs. Than some transformations and filters if required. And last configuration step is output to ES.

#### Input fconfiguration is:
```
cat <<EOF >/etc/logstash/conf.d/10-beats-input.conf
input {
  beats {
    port => 5044
  }
}
EOF
```
#### Transformation chain:
```
cat <<EOF >/etc/logstash/conf.d/50-filter.conf 
filter {
  grok {
    patterns_dir => ["/etc/logstash/patterns", "/etc/logstash/extra_patterns"]
    match => { "message" => [ "%{COMBINEDAPACHELOG}", "%{HTTPD_ERRORLOG}", "%{MAGENTO_DEBUG_LOG}" ] }
    overwrite => [ "message" ]
  }
  geoip {
    database => "/usr/share/GeoIP/GeoLiteCity.dat"
    source => "client_ip"
  }
}
EOF
```
#### Output:
```
cat <<EOF >/etc/logstash/conf.d/90-es-output.conf 
output {
  elasticsearch {
    hosts => ["10.10.80.7", "10.10.80.8"]
    manage_template => false
    index => "%{[@metadata][beat]}-%{+YYYY.MM.dd}"
    document_type => "%{[@metadata][type]}"
  }
}
EOF
```

# Grok Magic!

Put Magento Pattern into ```/etc/logstash/patterns```

```
cat <<EOF >/etc/logstash/patterns/magento_patterns
MAGENTO_DEBUG_LOG \[%{TIMESTAMP_ISO8601:timestamp}\]\s*%{WORD:module}\.%{LOGLEVEL:loglevel}:\s*%{WORD:method}:\s*%{GREEDYDATA:message}
EOF
```