cd /vagrant
sudo sed -i 's/ peer/ trust #peer/g' /etc/postgresql/9.5/main/pg_hba.conf
sudo sed -i 's/ md5/ trust #md5/g' /etc/postgresql/9.5/main/pg_hba.conf
sudo /etc/init.d/postgresql reload
psql -c 'create database coyote;' -U postgres
cp .env.default .env
make install-dev
php artisan key:generate