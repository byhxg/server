curl -sSL https://get.daocloud.io/docker | sh
curl -L https://get.daocloud.io/docker/compose/releases/download/1.24.1/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose
mkdir /etc/docker
echo '{
  "registry-mirrors": [
    "https://registry.docker-cn.com"
  ]
}' > /etc/docker/daemon.json && systemctl restart docker
docker network create --subnet=192.168.5.0/24 redis-network
