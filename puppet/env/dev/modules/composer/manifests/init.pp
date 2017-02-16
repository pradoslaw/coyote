# taken from http://johnstonianera.com/install-composer-with-puppet-and-vagrant/
# and fixed with https://github.com/puphpet/puphpet/issues/2098
# and added sudo chmod 755 /usr/local/bin/composer
class composer {
	exec { 'install composer':
		command => 'wget https://getcomposer.org/composer.phar -O composer.phar && sudo mv composer.phar /usr/local/bin/composer && sudo chmod 755 /usr/local/bin/composer'
	}
}