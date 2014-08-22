<?php

/**
 * @author Alaa Attya <vidooman@gmail.com>
 * @package virtualhost
 * @version 1.0.0
 * 
 * A command for turning virtualhost creation
 * into an automated process
 */
echo "\033[32mEnter project source directory [document root]: \033[37m";
$projectDir = trim(fgets(STDIN));


if (!is_dir($projectDir)) {
    die("\033[31mERROR directory not found!!\033[37m\n");
}

echo "setting project dir to: $projectDir\n";

echo "\033[32mEnter virtualhost url[ex: local.my-site.com]: \033[37m";
$vsName = trim(fgets(STDIN));

// Preparing config file
$vsConfig = "<virtualhost *:80>

    ServerAdmin admin@server.com
    DocumentRoot $projectDir
    ServerName $vsName
    ServerAlias $vsName

    <Directory $projectDir>
        AllowOverride All
    </Directory>

</VirtualHost>
";

// Remove .com from config filename
$vsConfigFileName = str_replace(".com", "", $vsName) . ".conf";

// Check if the virtualhost already exits
if (file_exists("/etc/apache2/sites-available/$vsConfigFileName")) {
    die("\033[31mERROR virtualhost already exits!!\033[37m\n");
}

try {

    file_put_contents("/etc/apache2/sites-available/$vsConfigFileName", $vsConfig);

    // Updating the hosts file
    $vs = "# add $vsConfigFileName\n127.0.0.1    $vsName";
    file_put_contents("/etc/hosts", $vs, FILE_APPEND);

    // Enable the new added virtualhost
    echo "Enable the new added virtualhost...\n";
    exec("a2ensite $vsConfigFileName");

    // Restart apache to take the effect
    echo "Restarting apache2...\n";
    exec("service apache2 restart");
} catch (\Exception $ex) {
    $error = $ex->getMessage();
    die("\033[31m$error\033[37m\n");
}

