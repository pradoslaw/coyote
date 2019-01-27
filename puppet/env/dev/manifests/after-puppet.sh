cd /vagrant
#sudo sed -i 's/ peer/ trust #peer/g' /etc/postgresql/9.5/main/pg_hba.conf
#sudo sed -i 's/ md5/ trust #md5/g' /etc/postgresql/9.5/main/pg_hba.conf

#postgresql: ustawiamy hasło na admina i tworzymy bazę
sudo -u postgres psql -c "alter user postgres password 'postgres';"
sudo -u postgres psql -c "create database coyote;"
#sudo -u postgres psql -c "create database coyote encoding='UTF-8' lc_collate='en_US.utf8' lc_ctype='en_US.utf8';"
#psql -c 'create database coyote;' -U postgres
#sudo /etc/init.d/postgresql reload
sudo service postgresql restart

#konfigutrujemy mongo do użycia lokalnie
#sudo sed -i 's/bind_ip = 127.0.0.1/#bind_ip = 127.0.0.1/g' /etc/mongodb.conf
#ustawiamy autoryzację dla mongo
#sudo sed -i 's/#auth = true/auth = true/g' /etc/mongodb.conf
sudo service mongodb restart
#mongo: dodajemy admina
#mongo admin --eval "db.createUser({user: 'coyote', pwd: 'coyote', roles: ['userAdminAnyDatabase', 'dbAdminAnyDatabase', 'readWriteAnyDatabase']})"

cp .env.default .env
#ustawiamy userów i hasła - upewniamy się, że user i hasło do Mongo są puste - inaczej dostaniemy auth failed
sudo sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=postgres/g' .env
sudo sed -i 's/^MONGO_USERNAME=.*/MONGO_USERNAME=/g' .env
sudo sed -i 's/^MONGO_PASSWORD=.*/MONGO_PASSWORD=/g' .env

make install-vagrant
php artisan key:generate
#php artisan config:clear
