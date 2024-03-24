#!/bin/bash

if [ "$1" = "d" ]; then
  sudo apt-get install docker docker-compose docker-ce docker-ce-cli containerd.io -y
elif [ "$1" = "t" ]; then
  echo ""
  echo "######################################"
  echo "   Telegram Messenger MTProto 安装    "
  echo "######################################"
  echo ""
  echo "* 使用 telegrammessenger 官方 docker 镜像 "
  echo "https://hub.docker.com/r/telegrammessenger/proxy/"
  echo ""
  echo " 安装时可能需要手动确认安装（回车即可）"
  echo ""
  echo "前序准备ing"
  ip=$(curl -4 ip.sb)
  hex=$(openssl rand -hex 16)
  read -p "请设置链接端口（1-65535）（默认随机生成）：" PORT
  if [[ -n $PORT ]]; then
    port=$PORT
  else
    port=$RANDOM
  fi
  echo "链接端口为 $port"
  echo ""
  read -p "请设置secret（回车自动生成）：" SECRET
  if [[ -n $SECRET ]]; then
    secret=$SECRET
  else
    secret=$hex
  fi
  echo " secret为 $secret"
  echo ""

  read -p "请设置容器名称（默认为 t_mtg）" DNAME
  if [[ -n $DNAME ]]; then
    name=$DNAME
  else
    name="t_mtg"
  fi
  echo "容器名称为 $name"
  echo ""
  docker stop $name
  docker rm -f $name
  docker pull telegrammessenger/proxy
  docker run -d -p$port:443 --name=$name --restart=always -v proxy-config:/data -e SECRET=$secret telegrammessenger/proxy:latest
  echo ""
  echo "tg://proxy?server=$ip&port=$port&secret=$secret"
  echo ""
  echo "配置文件位于 $name.txt 中"
  cat >./$name.txt <<EOF
tg://proxy?server=$ip&port=$port&secret=$secret
EOF
elif [ "$1" = "n" ]; then
  echo ""
  echo "######################################"
  echo "   nginx-mtproxy 安装    "
  echo "######################################"
  echo ""
  echo "* 使用 ellermister 制作的 nginx-mtproxy docker镜像 "
  echo "https://hub.docker.com/r/ellermister/nginx-mtproxy"
  echo ""
  echo " 安装时可能需要手动确认安装（回车即可）"
  echo ""
  echo "前序准备ing"
  ip=$(curl -4 ip.sb)
  hex=$(head -c 16 /dev/urandom | xxd -ps)
  read -p "请设置http端口（1-65535）（默认 80）：" PORT1
  if [[ -n $PORT1 ]]; then
    port1=$PORT1
  else
    port1=80
  fi
  echo "http端口为 $port1"
  echo ""
  read -p "请设置https端口（1-65535）（默认 443）：" PORT2
  if [[ -n $PORT2 ]]; then
    port2=$PORT2
  else
    port2=443
  fi
  echo "https端口为 $port2"
  echo ""
  read -p "请设置secret（回车自动生成）：" SECRET
  if [[ -n $SECRET ]]; then
    secret=$SECRET
  else
    secret=$hex
  fi
  echo "secret为 $secret"
  echo ""
  read -p "请设置伪装访问网站（默认为 bing.com）" DOMAIN
  if [[ -n $DOMAIN ]]; then
    domain=$DOMAIN
  else
    domain="bing.com"
  fi
  echo "伪装访问网址为 $domain"
  echo ""
  read -p "是否采用白名单模式（推荐，可应对爬虫和防火墙探测）[y/n]:" WHITE
  if [[ "$WHITE" =~ y|Y ]]; then
    white="IP"
    echo "请在搭建完成后访问 http://$ip:$port1/add.php 将ip加入白名单中"
  else
    white='OFF'
    echo "不开启白名单模式"
  fi
  echo ""
  read -p "是否使用tag[y/n]:" TAG
  if [[ "$TAG" =~ y|Y ]]; then
    echo "请将 $ip:$port"
    echo "secret: $secret"
    echo "发送给 @MTProxybot 以获取推广tag"
    read -p "请输入推广 tag：" tag
    echo "推广tag为 $tag"
  else
    tag=''
    echo "不使用tag"
  fi
  read -p "请设置容器名称（默认为 n_mtg）" DNAME
  if [[ -n $DNAME ]]; then
    name=$DNAME
  else
    name="n_mtg"
  fi
  echo "容器名称为 $name"
  echo ""

  docker stop $name
  docker rm -f $name
  docker pull ellermister/nginx-mtproxy
  docker run --name $name -d -e secret="$secret" -e domain="$domain" -e tag="$tag" -e ip_white_list=$white -p $port1:80 -p $port2:443 ellermister/nginx-mtproxy:latest
  if [[ "$WHITE" =~ y|Y ]]; then
    echo ""
    echo "请在搭建完成后访问 http://$ip:$port1/add.php 将ip加入白名单中"
  fi
  echo ""
  echo "查看连接信息：docker logs $name"
  echo ""
elif [ "$1" = "g" ]; then
  echo ""
  echo "######################################"
  echo "     Highly-opionated MTPROTO 安装    "
  echo "######################################"
  echo ""
  echo "* 使用 nineseconds 制作的 mtproto go docker镜像 "
  echo "https://hub.docker.com/r/nineseconds/mtg"
  echo ""
  echo " 安装时可能需要手动确认安装（回车即可）"
  echo ""
  echo "前序准备"
  ip=$(curl -4 ip.sb)
  echo ""
  echo "准备完毕～"
  echo ""
  read -p "请设置链接端口（1-65535）（默认随机生成）：" PORT
  if [[ -n $PORT ]]; then
    port=$PORT
  else
    port=$RANDOM
  fi
  echo "链接端口为 $port"
  echo ""
  read -p "请设置内部端口（1-65535）（默认随机生成）：" PORTS
  if [[ -n $PORTS ]]; then
    ports=$PORTS
  else
    ports=$RANDOM
  fi
  echo "内部端口为 $ports"
  echo ""
  read -p "请设置伪装访问网站（默认为 bing.com）" DOMAIN
  if [[ -n $DOMAIN ]]; then
    domain=$DOMAIN
  else
    domain="bing.com"
  fi
  echo "伪装访问网址为 $domain"
  echo ""
  read -p "请设置容器名称（默认为 g_mtg）" DNAME
  if [[ -n $DNAME ]]; then
    name=$DNAME
  else
    name="g_mtg"
  fi
  echo "容器名称为 $name"
  echo ""
  secret=$(docker run --rm nineseconds/mtg:master generate-secret --hex $domain)
  cat >/etc/mtg.toml <<EOF
secret = "$secret"
bind-to = "0.0.0.0:$ports"
EOF
  docker stop $name
  docker rm -f $name
  docker run -d --name $name -v /etc/mtg.toml:/config.toml -p $port:$ports --restart=always nineseconds/mtg:master

  echo ""
  echo "配置文件位于 $name.txt 中"
  cat >./$name.txt <<EOF
tg://proxy?server=$ip&port=$port&secret=$secret
EOF
  echo ""
  echo "tg://proxy?server=$ip&port=$port&secret=$secret"
  echo ""
elif [ "$1" = "s" ]; then
  echo ""
  echo "######################################"
  echo "     SOCKS5 安装    "
  echo "######################################"
  echo ""
  echo "* 使用 nadoo 制作的 glider镜像 "
  echo "https://hub.docker.com/r/nadoo/glider"
  echo ""
  echo " 安装时可能需要手动确认安装（回车即可）"
  echo ""
  echo "前序准备"
  ip=$(curl -4 ip.sb)
  read -p "请设置链接端口（1-65535）（默认随机生成）：" PORT
  if [[ -n $PORT ]]; then
    port=$PORT
  else
    port=$RANDOM
  fi
  echo "链接端口为 $port"
  echo ""
  read -p "请设置内部端口（1-65535）（默认随机生成）：" PORTS
  if [[ -n $PORTS ]]; then
    ports=$PORTS
  else
    ports=$RANDOM
  fi
  echo "内部端口为 $ports"
  echo ""
  read -p "请设置认证帐号（默认：admin）" USER
  if [[ -n $USER ]]; then
    user=$USER
  else
    user="admin"
  fi
  echo "认证帐号为 $user"
  echo ""
  read -p "请设置认证密码（默认：admin）" PASS
  if [[ -n $PASS ]]; then
    pass=$PASS
  else
    pass="admin"
  fi
  echo "认证密码为 $pass"
  echo ""
  read -p "请设置容器名称（默认为 s_proxy）" DNAME
  if [[ -n $DNAME ]]; then
    name=$DNAME
  else
    name="s_proxy"
  fi
  echo "容器名称为 $name"
  echo ""
  docker stop $name
  docker rm -f $name
  docker run -dit -p $port:$ports --name $name --restart=always nadoo/glider -verbose -listen $user:$pass@:$ports
  echo ""
  echo "tg://socks?server=$ip&port=$port&user=$user&pass=$pass"
  echo "配置文件位于 $name.txt 中"
  echo ""
  cat >./$name.txt <<EOF
tg://socks?server=$ip&port=$port&user=$user&pass=$pass
EOF
elif [ "$1" = "stop" ]; then
  docker stop $(docker ps -aq)
elif [ "$1" = "del" ]; then
  docker rm $(docker ps -aq)
elif [ "$1" = "delete" ]; then
  docker stop $(docker ps -aq)
  docker rm $(docker ps -aq)
else
  echo "安装GO_MTPROTO：g"
  echo "安装SOCKS5：s"
  echo "停止全部容器：stop"
  echo "删除全部容器：del"
  echo "停止删除全部：delete"
  echo "安装docker：d"
  echo "安装tg官方MTPROTO：t"
  echo "安装nginx_MTPROTO：n"
fi