# -*- mode: ruby -*-
# vi: set ft=ruby :

MACHINE_IP = "175.0.0.10"
PROJECT_LOCATION = "/gc"

module OS
    def OS.windows?
        (/cygwin|mswin|mingw|bccwin|wince|emx/ =~ RUBY_PLATFORM) != nil
    end
end

raise "Only for windows. (Docker-toolbox setup)" unless OS.windows?

Vagrant.configure("2") do |config|
    config.vm.box = "ubuntu/xenial64"
    config.vm.network "private_network", ip: MACHINE_IP
    config.ssh.forward_agent = true

    if OS.windows?
        config.vm.synced_folder "./", PROJECT_LOCATION, type: "nfs"
    else
        config.vm.synced_folder "./", PROJECT_LOCATION,
            mount_options: ["noatime,intr,nordirplus,nolock,async,noacl,fsc,tcp"],
            type: "nfs"
    end

    config.vm.boot_timeout = 9000

    config.vm.provider "virtualbox" do |vb|
        # Use VBoxManage to customize the VM. For example to change memory:
        vb.customize ["modifyvm", :id, "--memory", "2048"]
        vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
        vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
        vb.customize ["modifyvm", :id, "--uartmode1", "disconnected"]
    end

    config.vm.provision "shell", env: {"PROJECT_LOCATION" => PROJECT_LOCATION}, inline: <<-SHELL
        curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
        sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
        sudo apt-get update
        sudo apt-get -y upgrade
        sudo apt-get install -y docker-ce

        sudo curl -sL https://github.com/docker/compose/releases/download/1.16.1/docker-compose-`uname -s`-`uname -m` -o /usr/local/bin/docker-compose
        sudo chmod +x /usr/local/bin/docker-compose

        curl -sL https://github.com/docker/machine/releases/download/v0.12.2/docker-machine-`uname -s`-`uname -m` >/tmp/docker-machine
        chmod +x /tmp/docker-machine
        sudo cp /tmp/docker-machine /usr/local/bin/docker-machine

        echo "PS1='\${debian_chroot:+(\$debian_chroot)}\\[\\033[01;32m\\]\\u@\\h\\[\\033[00m\\]:\\[\\033[01;34m\\]\\w\\[\\033[00m\\]\\$ '" >> /home/ubuntu/.bashrc
        echo "cd ${PROJECT_LOCATION}" >> /home/ubuntu/.bashrc
        cd ${PROJECT_LOCATION}
    SHELL
end
