# Vagrant file specific for version 2
Vagrant.configure("2") do |config|

	config.vm.box = "bento/ubuntu-16.10"
	config.vm.hostname = "coyote.dev"
	config.vm.network :private_network, ip: "192.168.10.10"
	#nginx
	config.vm.network :forwarded_port, guest: 80, host: 8080
	#postgresql
	config.vm.network :forwarded_port, guest: 5432, host: 5433
	config.ssh.forward_agent = true

	# Specify folder which you would like to have available in your box
	#config.vm.synced_folder ".", "/vagrant"

	# In case speed is lacking, try the NFS option
	config.vm.synced_folder ".", "/vagrant", :nfs => true

	# Specific configuration options for Virtualbox
	config.vm.provider "virtualbox" do |v|

		# Show gui instead of default, which is headless
		v.gui = false

		# Use modern chipset
		v.customize ["modifyvm", :id, "--chipset", "ich9"]

		# Increase default memory size
		v.customize ["modifyvm", :id, "--memory", 1024]

		# Dual core
		v.customize ["modifyvm", :id, "--cpus", 2]

		#prevent npm issues
		v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate//vagrant", "1"]
	end

	# Install puppet
	config.vm.provision "shell", path: "puppet/env/dev/manifests/install-puppet.sh"

	# Start puppet
	config.vm.provision :puppet do |puppet|
		puppet.environment = "dev"
		puppet.environment_path = "puppet/env"
		puppet.manifests_path = 'puppet/env/dev/manifests'
		puppet.manifest_file = 'init.pp'
		puppet.module_path = 'puppet/env/dev/modules'
	end

	# Install Coyote
	config.vm.provision "shell", path: "puppet/env/dev/manifests/after-puppet.sh"
end