# taken from https://github.com/JustinCarmony/PHP-Workers-Tutorial/blob/master/vagrant/puppet/manifests/base.pp
class supervisor {
	#require workers
	package { "supervisor":
		ensure => installed,
	}

	service { "supervisor":
		enable => true,
		ensure => running,
		#hasrestart => true,
		#hasstatus => true,
		require => Package["supervisor"],
	}

#	file { "/etc/supervisor/supervisord.conf":
#		ensure => file,
#		source => '/puppet-files/etc/supervisor/supervisord.conf',
#		notify => Service['supervisor'],
#		require => Package["supervisor"]
#	}
}