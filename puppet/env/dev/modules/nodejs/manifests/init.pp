# see also http://dev.alexishevia.com/2013/09/setting-up-nodejs-dev-environment-with.html
class nodejs {
	package { 'nodejs':
		ensure => present
	}

	# Because of a package name collision, 'node' is called 'nodejs' in Ubuntu.
	# Here we're adding a symlink so 'node' points to 'nodejs'
	file { '/usr/bin/node':
		ensure => 'link',
		target => "/usr/bin/nodejs",
	}
}