class postgresql {
	package { 'postgresql':
		ensure => present
	}

	service { 'postgresql':
		ensure => running,
		require => Package['postgresql'],
	}
}