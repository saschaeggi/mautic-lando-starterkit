# Mautic Lando Starterkit

A Mautic installation

## Prerequisites

### Lando
Install Lando: https://docs.lando.dev/basics/installation.html

### Composer
Install composer: https://getcomposer.org/

---

## Installation

### Clone repo
Clone the repo
```
git clone git@github.com:saschaeggi/mautic-lando-starterkit.git drupal-mautic-starterkit && cd mautic-lando-starterkit
```

### Let's build the app
```
lando start
```

### You're ready to go
https://mautic.lndo.site/

---

## Commands

### Start containers
```
lando start
```

### Stop containers
```
lando stop
```

#### Poweroff Lando
```
lando poweroff
```

### SSH into container
```
lando ssh
```

### Mautic command line tool
```
lando mt
```

---

## Mautic
https://mautic.lndo.site

```
username: admin
password: mautic
```

## Mailhog
http://mail.mautic.lndo.site/

## Database
```
username: mautic
password: mautic
database: mautic
host: database
```
