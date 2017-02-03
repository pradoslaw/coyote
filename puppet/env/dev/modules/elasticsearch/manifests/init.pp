# taken from http://www.jeroenreijn.com/2014/11/using_vagrant_with_puppet.html
# see also https://github.com/Spantree/vagrant-puppet-elasticsearch-cluster-example/blob/master/puppet/manifests/base.pp
class elasticsearch {
	file { "/tmp/install_elasticsearch.sh":
		ensure	=> present,
		mode		=> '0775',
		source	=> "puppet:///modules/elasticsearch/install_elasticsearch.sh"
	}

	exec { "install_elasticsearch":
		command => "/bin/bash /tmp/install_elasticsearch.sh",
		path => "/usr/bin:/usr/local/bin:/bin:/usr/sbin:/sbin",
		timeout => 0,
		require => File["/tmp/install_elasticsearch.sh"]
	}
}

class { 'elasticsearch':
	package_url => 'https://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-1.3.2.deb',
	config => {
		'cluster.name' => 'project-cluster',
		'network' => {
			'host' => '0.0.0.0',
		}
	},
	java_install => true,
	require => Exec["install_elasticsearch"]
}

elasticsearch::instance { 'es-01': }

elasticsearch::plugin{'mobz/elasticsearch-head':
	module_dir => 'head',
	instances => [ 'es-01' ],
}