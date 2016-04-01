# Kibana

## Installation

Repo installation as usual. Add PGP key first

```
rpm --import https://packages.elastic.co/GPG-KEY-elasticsearch
```
Add rpm repo file in a next step

```
cat <<EOF >/etc/yum.repos.d/kibana.repo 
[kibana-4.4]
name=Kibana repository for 4.4.x packages
baseurl=http://packages.elastic.co/kibana/4.4/centos
gpgcheck=1
gpgkey=http://packages.elastic.co/GPG-KEY-elasticsearch
enabled=1
EOF
```
And finally just install

```
yum -i install kibana
```

Now we can start as ES as Kibana

```
service kibana start
service elasticsearch start
```

## Configuration
### Additional ES configs
#### Elastic way!
Kibana can hold only one IP as ES-source. Actualy we have two ways to configure access to ES cluster. 

- install ES on kibana server in Tribe mode 
- create Azure Load Balancer. 

Install Elasticsearch in Tribe mode:

Add rpm repo as usual

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
chkconfig --add elasticsearch
```
Tribe configuration for ES:

```
cat <<EOF >/etc/elasticsearch/elasticsearch.yml 
cluster.name: beats
node.name: kibana
node.data: false
node.master: false
network.host: _eth0_
http.port: 9200

discovery.zen.ping.unicast.hosts: ["10.10.80.7", "10.10.80.8"]
EOF
```
#### Azure way
To be reproduced

#### Final Kibana Config

Open kibana configuration file in ```/opt/kibana/config/kibana.yml```
And fix two records:

```elasticsearch.url: "http://10.10.80.9:9200"```

```elasticsearch.preserveHost: true```

Last record is really importance. If ```elasticsearch.preserveHost``` is set to FALSE browser will try to connect on ES:9200 port directly. We do not want open this staff ourside.

## Plugins
We really ned one awesome Kibana plugin before any next steps. This plugin can be used as command line interface to the ES instances. Nice way to control ES cluster and understand it's health. Installation really easy!

```
/opt/kibana/bin/kibana plugin --install elastic/sense
```

## Go!
Now we are ready to start Kibana
And do not forget to try Kibana ready to use dashboards

```
git clone https://github.com/elastic/beats-dashboards.git
cd beats-dashboards/
./load.sh -url "http://10.10.80.7:9200"
```
