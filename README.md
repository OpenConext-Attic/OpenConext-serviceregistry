# This project has been made obsolete by SURFConext-Deploy! #
See: https://github.com/SURFconext/SURFconext-deploy/tree/master/ansible/roles/janus



# OpenConext Service Registry #

ServiceRegistry (SR) is a web-based registry for managing federation metadata, including
SAML2 SP and IdP metadata, ARP and ACL information.  It is currently also used to register
the OAuth metadata of entities that work with the OpenConext platform.  The product is
based on JANUS, originally developed by WAYF, the Danish Federation Operator and now
maintained by SURFnet.  Only a few minor configuration extensions were applied to implement
JANUS into the ServiceRegistry module of OpenConext.  For more information on JANUS, please
visit http://code.google.com/p/janus-ssp/.

The SR allows the following:
1. administering of SAML2 metadata for the federation.
2. automated access via the REST api to the metadata information.

## License

See the LICENSE-2.0.txt file

## Disclaimer

See the NOTICE.txt file

## System Requirements ##
* Linux
* Apache with modules:
    - mod_php
* PHP 5.3.x.
* MySQL > 5.x with settings:
    - default-storage-engine=InnoDB (recommended)
    - default-collation=utf8_unicode_ci (recommended)
* EngineBlock

**NOTE**
While care was given to make EngineBlock as compliant as possible with mainstream Linux distributions,
it is only regularly tested with RedHat Enterprise Linux and CentOS.

## Installation ##

If you are reading this then you've probably already installed a copy of EngineBlock somewhere on the destination server,
if not, then that would be step 1 for the installation.

If you have an installed copy and your server meets all the requirements above, then please follow the steps below
to start your installation.


### First, create an empty database ###

**EXAMPLE**
```sh
    mysql -p
    Enter password:
    Welcome to the MySQL monitor.  Commands end with ; or \g.
    Your MySQL connection id is 21
    Server version: 5.0.77 Source distribution

    Type 'help;' or '\h' for help. Type '\c' to clear the buffer.

    mysql> create database serviceregistry default charset utf8 default collate utf8_unicode_ci;
```
### Check out the application ###

Before you can start configuring you have to check out the application (if you didn't already do so). e.g. put it
in ``sh /var/www``. Get a prepacked tarball from Github: https://github.com/OpenConext/OpenConext-serviceregistry/releases

### Then configure the application ###

Copy over the example configuration files and directory from the `` docs/etc/`` directory to `` /etc/surfconext/``:
```sh
    sudo mkdir /etc/surfconext
    sudo cp -Rvf docs/etc/* /etc/surfconext/
```
Then edit the copied files with your favorite editor and review the settings to make sure it matches your configuration.

Install the database schema for JANUS
``sh bin/migrate``

Note that the initial installation adds the 'admin' user AND an 'engine' user with the secret 'engineblock'.
It is recommended that you change the password of the 'engine' user for production setups with the following SQL statement:
```sql
    UPDATE `janus`.`janus__user` SET `secret` = 'MYSECRET' WHERE `janus__user`.`userid` ='engine';
```

### Configure HTTP server ###

**EXAMPLE**

Make sure you have the following alias (or it's functional equivalent):
```
    Alias /simplesaml /var/www/serviceregistry/www
```
Note that the Service Registry SHOULD run on HTTPS, you can redirect users from HTTP to HTTPS
with the following Apache rewrite rules on a *:80 VirtualHost:
```
    RewriteEngine   on
    RewriteCond     %{SERVER_PORT} ^80$
    RewriteRule     ^(.*)$ https://%{SERVER_NAME}$1 [L,R=301]
```

### Bootstrap the Service Registry ###

1. Log in to JANUS with the admin user

    Go to your Service Registry instance.
    Go to the **Federation** tab.
    Click **JANUS module**.
    Log in with the admin user and the password you configured in `` /etc/surfconext/serviceregistry.config.php``.

2. Add the Service Registry as an SP in JANUS

    The Service Registry logs in to the EngineBlock that it supplies with it's data.
    This is wonderfully cyclic, but it does mean that while in admin mode you have to add the Service Registry
    as a Service Provider in it's self.

    You can find the metadata for the Service Registry as a Service Provider with the following:
    Go to your Service Registry instance.
    Go to the **Federation** tab.
    Click \[ Show metadata \].

3. Add Identity Providers

    Add at least one Identity Provider that you can use to log in to the Service Registry later.


### Test your EngineBlock instance ###

Go to your Service Registry instance.
Go to the **Authentication** tab.
Click **Test configured authentication sources**
Click **default-sp**.

You should now be able to log in successfully via your configured EngineBlock instance.


### Switch to Single Sign On via EngineBlock ###

Edit ``/etc/surfconext/serviceregistry.module_janus.php`` and change:
```
    $config['auth'] = 'admin'; // Admin password (for installing or debugging)
    #$config['auth'] = 'default-sp'; // Single Sign On via EngineBlock
```

To:
```
    #$config['auth'] = 'admin'; // Admin password (for installing or debugging)
    $config['auth'] = 'default-sp'; // Single Sign On via EngineBlock
```

And enjoy your new Service Registry instance!


## Updating ##

It is recommended practice that you deploy the Service Registry in a directory that includes
the version number and use a symlink to link to the 'current' version of the Service Registry.

**EXAMPLE**
```sh
    .
    ..
    serviceregistry -> serviceregistry-v1.6.0
    serviceregistry-v1.5.0
    serviceregistry-v1.6.0
```

If you are using this pattern, an update can be done with the following:

1. Download and deploy a new version in a new directory.

2. Check out the release notes in ``docs/release_notes/X.Y.Z.md`` (where X.Y.Z is the version number) for any
   additional steps.

4. Change the symlink.

5. Run the database migrate script.
``sh bin/migrate``
