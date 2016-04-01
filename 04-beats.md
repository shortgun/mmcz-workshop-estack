# Beats
## Do not forget about GeoIP
# GeoIP installation for Logstash

```
rpm -ivh http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
yum -y install GeoIP
curl http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz -o /usr/share/GeoIP/GeoLiteCity.dat.gz && gunzip /usr/share/GeoIP/GeoLiteCity.dat.gz
```

## Installation
Repo installation as usual. Add PGP key first

```
rpm --import https://packages.elastic.co/GPG-KEY-elasticsearch
```
Download and install packages

```
curl -o topbeat-1.2.0-x86_64.rpm https://download.elastic.co/beats/topbeat/topbeat-1.2.0-x86_64.rpm
curl -o filebeat-1.2.0-x86_64.rpm https://download.elastic.co/beats/filebeat/filebeat-1.2.0-x86_64.rpm
curl -o packetbeat-1.2.0-x86_64.rpm https://download.elastic.co/beats/packetbeat/packetbeat-1.2.0-x86_64.rpm
```
And finally just install

```
yum localinstall *.rpm
```

Do not start beats until fully configured

## Configuration
### packet
####Set up configuration file

```
cat <<EOF >/etc/packetbeat/packetbeat.yml
interfaces:
  device: eth0
protocols:
#  dns:
#    ports: [53]
#    include_authorities: true
#    include_additionals: true
  http:
    ports: [80, 8080]
    hide_keywords: ['pass', 'password', 'passwd']
    send_headers: ["Host", "User-Agent"]
    redact_authorization: true
    send_response: false
    split_coookie: false
#    real_ip_header: "X-Forwarded-For"   
  memcache:
    ports: [11211]
  mysql:
    ports: [3306]
    max_rows: 3
    max_row_length: 256
  redis:
    ports: [6379]
output:
  logstash:
    hosts: ["10.10.80.7:5044", "10.10.80.8:5044"]
    index: "packetbeat"
shipper:
  tags: ["magento", "workers"]
  geoip:
    paths:
      - "/usr/share/GeoIP/GeoLiteCity.dat"
logging:
  files:
    rotateeverybytes: 10485760 # = 10MB
EOF
```

#### load template

```
curl -XPUT 'http://10.10.80.7:9200/_template/packetbeat' -d@/etc/packetbeat/packetbeat.template.json
```

### top
####Set up configuration file

```
cat <<EOF >/etc/topbeat/topbeat.yml
input:
  period: 10
  procs: ["(httpd|mysql)"]
  stats:
    system: true
    proc: true
    filesystem: true
    cpu_per_core: false
output:
  elasticsearch:
    hosts: ["10.10.80.7", "10.10.80.8"]
    index: "topbeat"
shipper:
  tags: ["magento", "workers"]
  refresh_topology_freq: 10
  topology_expire: 15
  geoip:
    paths:
      - "/usr/share/GeoIP/GeoLiteCity.dat"
logging:
  files:
    rotateeverybytes: 10485760 # = 10MB
EOF
```
#### load template

```
curl -XPUT 'http://10.10.80.7:9200/_template/topbeat' -d@/etc/topbeat/topbeat.template.json
```

### filebeat
####Set up configuration file

```
cat <<EOF >/etc/filebeat/filebeat.yml
filebeat:
  prospectors:
    -
      paths:
        - "/var/log/httpd/*_log"
      input_type: log
      fields_under_root: true
      document_type: apache
      scan_frequency: 10s
    -
      paths:
        - "/var/www/html/var/log/debug.log"
      input_type: log
      fields_under_root: true
      document_type: magento
      scan_frequency: 10s
  registry_file: /var/lib/filebeat/registry
output:
  logstash:
    hosts: ["10.10.80.7:5044", "10.10.80.8:5044"]
    index: filebeat
shipper:
  tags: ["magento", "workers"]
logging:
  files:
EOF
```

#### load template

```
curl -XPUT 'http://10.10.80.7:9200/_template/filebeat' -d@/etc/filebeat/filebeat.template.json
```
