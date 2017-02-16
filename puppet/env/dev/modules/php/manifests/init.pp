class php {
	package { ['php7.0-fpm',
		'php-gd',
		'php-mbstring',
		'php-mcrypt',
		'php-pear',
		'php-curl',
		'php-mongodb',
		'php-pgsql',
		'php7.0-cli']:
		ensure => present,
		require => Exec['apt-get update'],
	}

	service { 'php7.0-fpm':
		ensure => running,
		require => Package['php7.0-fpm'],
	}

	#default permissions (600) makes php-fpm and nginx fails to start
	#commented out as it solves only this problem - probably the root cause is somewhere else
	#exec { 'set error_log permissions':
	#	command => 'touch /var/log/php7.0-fpm.log && sudo chmod 666 /var/log/php7.0-fpm.log'
	#}
}