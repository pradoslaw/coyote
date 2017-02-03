class postgresql {
	package { ['postgresql', 'postgresql-contrib']:
		ensure => present,
		require => Exec['apt-get update'],
	}

	service { 'postgresql':
		ensure => running,
		require => Package['postgresql'],
	}
}