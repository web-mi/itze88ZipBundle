itze88ZipBundle
===============

Symfony2 ZIP Service Bundle with minimum memory usage

## Installation

### Step 1: Download itze88ZipBundle

**Using the vendors script**

Add the following lines in your `deps` file:

```
[itze88ZipBundle]
    git=git://github.com/itze88/itze88ZipBundle.git
    target=bundles/itze88/ZipBundle
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

**Using submodules**

If you prefer instead to use git submodules, the run the following:

``` bash
$ git submodule add git://github.com/itze88/itze88ZipBundle.git vendor/bundles/itze88/ZipBundle
$ git submodule update --init
```

### Step 2: Configure the Autoloader

Add the `itze88` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'itze88' => __DIR__.'/../vendor/bundles',
));
```

### Step 3: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new itze88\ZipBundle\itze88ZipBundle(),
    );
}
```

## Accessing the Zip service

The ZIP Manager is available in the container as the `itze88.zip.manager` service.

``` php
$zipManager = $container->get('itze88.zip.manager');
```

## Creating a new ZIP-File

A new ZIP-File can be created by setting the output-path in the ZIP-Manager.

``` php
$zipManager->setOutputFile('/var/www/symfony2/web/downloadableContent/');
```

**Note:**

> The path always have to endup with /

## Add something to the ZIP-File

### The hard but flexible way

#### Add single directory to zip

    Now you can add some directorys to your zip

``` php
    $zm->addDir('zipFolder1/');
```

**Note:**

> With this method you have to add each directory

**For expample:**

``` php
    //First add 2 folders in the root of the zip
    $zm->addDir('folder1/');
    $zm->addDir('folder2/');

    //Then you have to add the subfolders
    $zm->addDir('folder1/subfolder1');
    $zm->addDir('folder1/subfolder2');
    $zm->addDir('folder1/subfolder1/subsubfolder1');
    $zm->addDir('folder2/subfolder1');
```

#### Add single file to zip

Now you can add a file to your zip

``` php
    $zm->addFile('/var/www/symfony2/availableContent/zipThis/thisFile.txt', 'thisFile.txt');
```

**Note:**

> With this method you have to create the hole structure

**For expample:**

> If you want to add a File /var/www/symfony2/availableContent/folder1/thisFile.txt
> and you want to hold the structure form folder1/

``` php
    //First add the Folder to the zip
    $zm->addDir('zipThis/');

    //Then you have to add the files
    //First parameter is where the file exists on file system 
    //and second where it should appear in zip and with which name
    $zm->addFile('/var/www/symfony2/availableContent/zipThis/thisFile.txt', 'zipThis/thisFile.txt');

```


### Add a complete directory on file system to zip (the easy way)

#### Add to root in ZIP-File

``` php
$zm->addDirectory('/var/www/symfony2/availableContent/zipThis/');
```

**For expample:**

> If zipThis/ has over 100 Files and you download it and unzip it, all files will appear in the directory where you unzip the file
> If you have more than one File you should use the next Method

#### Add to subdirectory in ZIP-File

``` php
$zm->addDirectory('/var/www/symfony2/availableContent/zipThis/', 'addEverythingToThisZipFolder/');
```

**For expample:**

> If zipThis/ has over 100 Files and you download it and unzip it, all files will appear in one directory addEverythingToThisZipFolder/

>>>>>>> branch 'master' of https://github.com/itze88/itze88ZipBundle.git


**Credits**
Original source (CreateZipFile class) was created by Rochak Chauhan - www.rochakchauhan.com.
Modified by ironhawk, attilaw '@' cygnussystems.hu
Bundled and extended by Marc Itzenthaler (github(at)web-mi.de), on GitHub:(https://github.com/itze88)
