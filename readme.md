# COMMANDES


Lancer
```bash
docker-compose up -d
```
Entrer dans le dossier project du container
```bash
docker exec -ti www_ecommerce_sf7 bash
cd project
```
Arrêter les container
```bash
docker-compose down
```
Builder
```bash
docker-compose up --build -d
```

Donner les droits  
```bash
sudo chown -R $USER ./
```
## Access

The application:
http://127.0.0.1:8000/

phpMyAdmin:
http://127.0.0.1:8080/

MailCatcher:
http://127.0.0.1:1080/



#### ATTENTION : Commandes pour éffacer des données 

Supprime tous les conteneurs arrêtés, toutes les images non utilisées, et tous les volumes non attachés  
```bash
docker system prune
```
Même chose mais en supprimant les images en cache
```bash
docker system prune
```
Supprime toutes les images de manière forcée, y compris celles utilisées par des conteneurs en cours d'exécution.
```bash
docker rmi $(docker images -q) --force
```
Arrête les services et supprime les volumes associés à ces services.  
```bash
docker-compose down --volumes
```

