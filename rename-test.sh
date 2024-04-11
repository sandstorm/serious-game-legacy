################ Defaults #################

defaultVendorName="MyVendor"
defaultPackageName="AwesomeNeosProject"

defaultVendorNameLowerCase=$(echo $defaultVendorName | tr '[:upper:]' '[:lower:]')
defaultPackageNameLowerCase=$(echo $defaultPackageName | tr '[:upper:]' '[:lower:]')

defaultDockerHubPath="infrastructure/neos-on-docker-kickstart"

############### User Input ################

_red_echo "All Data in the DB will be lost. The dump will be imported again once"
_red_echo "you start up the project. "
echo
_yellow_echo "If you want to keep your data hit CTRL+C to exit and run 'dev site-export'"
_yellow_echo "before running the kickstart again."
echo
_yellow_echo "Hit RETURN to continue with the kickstart"

read -p ""

_green_echo "Please Provide the following information"
read -p "Vendor (default='$defaultVendorName'): " vendorName
vendorName=${vendorName:-$defaultVendorName}

read -p "Package (default='$defaultPackageName'): " packageName
packageName=${packageName:-$defaultPackageName}

vendorNameLowerCase=$(echo $vendorName | tr '[:upper:]' '[:lower:]')
packageNameLowerCase=$(echo $packageName | tr '[:upper:]' '[:lower:]')

_yellow_echo "This is what we will do next"
_red_echo "  * we will remove your docker containers and all their data"
echo "  * we do a search replace on vendor and package names"
echo "     * e.g. Flow packages names will be renamed to '${vendorName}.${packageName}'"
echo "     * e.g. the composer packageName will be renamed to '${vendorNameLowerCase}/${packageNameLowerCase}'"
echo "     * e.g. in the SQL dump we will also replace NodeType names"
echo "     * ..."
echo "  * we will remove some parts of the README that will be obsolete after running the kickstart"
echo "  * we remove some kickstarter files"
echo "  * we will try to create a Kubernetes namespace for you"
echo "  * you will later be asked if you want to init a new .git with a new remote"
echo

_yellow_echo "Hit RETURN to run the kickstart, or CTRL+C to exit"
read -p ""

# regex pattern for excluding paths and files
findExcludePaths="(/node_modules/|^./app/Packages|^./app/Build|^./tmp|.idea|.git|/kickstart.sh)"

# replace Vendor and Package name -> yaml, php, package.json ...
# grep -I ignores binary files, q suppresses output, E allows for extended regex, v inverts the match
find . -type f | grep -I -Ev ${findExcludePaths} | LC_CTYPE=C xargs -I% sed -i "" "s/${defaultVendorName}/${vendorName}/g" %
find . -type f | grep -I -Ev ${findExcludePaths} | LC_CTYPE=C xargs -I% sed -i "" "s/${defaultPackageName}/${packageName}/g" %
find . -type f | grep -I -Ev ${findExcludePaths} | LC_CTYPE=C xargs -I% sed -i "" "s/${defaultVendorNameLowerCase}/${vendorNameLowerCase}/g" %
find . -type f | grep -I -Ev ${findExcludePaths} | LC_CTYPE=C xargs -I% sed -i "" "s/${defaultPackageNameLowerCase}/${packageNameLowerCase}/g" %

echo "Vendor and Package name replaced"
