# Base DOCKER php8.3 Mysql and mailcatcher

### Modify the docker-compose to customize the names of the containers:
#### This is optional, change the name of:
* customNameDB_Msql
* customNamePhpMyAdmin
* customNameProject
* mailcatcher

### If the images have not yet been created:

```bash
docker-compose build 
docker-compose up -d
```

#### Or
```bash
docker-compose up --build -d
```


### If the images have already been created:
#### And no modifications have been made to the configuration files

```bash
docker-compose up -d
```
## Enter the container:
#### Attention! Modify "customNameProject" with the name of the container modified previously
`docker exec -ti customNameProject bash`
### Install the Symfony project:

`composer create-project symfony/skeleton:"7.0.*" project`   
`cd project`  
`composer require webapp`  


### At the root of the Docker environment, run this command (linux)
This will give you rights on all the content of the "project" folder.
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

## Configuration of DATABASE_URL in .env.local file:

`DATABASE_URL="mysql://root:@customNameContainerMySQL:3306/customNameDATABASE?serverVersion=8.3.0&charset=utf8mb4"`

##### Note:
- For users, we can also add the value of the "MYSQL_USER" and "MYSQL_PASSWORD" variables instead of 'root' (without password)

- "customNameContainerMySQL:3306": we cannot use 127.0.0.1:3306 because it does not point to the database container, but to the container itself.
  So, instead of 127.0.0.1:3306, we need to put the name of the container to which we need to point.

- "customNameDATABASE" is indicated in the docker-compose. It's the name of the database that is automatically created during the 'build'

- "serverVersion=8.3.0" Modify "8.3.0" by the MySQL version. To check in phpMyAdmin/variables/search "version"

## Configuration of MAILER_DSN in .env.local file:
`MAILER_DSN=smtp://mailcatcher:1025`
##### Note:
We would be tempted to do "MAILER_DSN=smtp://127.0.0.1:1025", but it will not work, we will have an error of the type:

`connection could not be established with host "127.0.0.1:1025": stream_socket_client(): Unable to connect to 127.0.0.1:1025 (Connection refused)`

WHY: Symfony is not able to connect to Mailcatcher on 127.0.0.1:1025 because the Symfony application runs in a separate Docker container and 127.0.0.1 does not point to the Mailcatcher container, but to the container itself.

So, instead of 127.0.0.1, we need to put the name of the container to which we need to point.

By doing a docker ps, we can see its name, which will be identical to that indicated in the docker-compose, or automatically generated if no specific name is specified.



## Useful commands:

### Build and start all services defined in your docker-compose.yml file.
###### NOTE: to use if modifications of Dockerfile or docker-compose.yml files:

```bash
docker-compose up --build -d
```


### Open an interactive shell inside the Docker container :



```bash
docker exec -ti customNameProject bash
```


### Stop all running containers:

```bash
docker-compose down
```




### Start Docker containers in the background:



```bash
docker-compose up -d
```


  
  


## *** Caution ***
### To remove all Docker images at once without specifying the name or ID of each image


```bash
docker rmi $(docker images -q) --force
```
### Stop and remove not only the containers, but also the volumes
#### Allows taking into account the modifications of the docker-compose on the database
#### **Attention**: The use of --volumes will remove all data stored in the Docker project volumes, which is irreversible.
`docker-compose down --volumes`
