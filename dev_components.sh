#!/bin/bash

source ./dev_utilities.sh

function list-components {
    componentsPackageNamespace="Sandstorm.ComponentLibrary"
    componentsPackagePath="./app/DistributionPackages/$componentsPackageNamespace"

    yq eval ".$componentsPackageNamespace.Components | keys" "$componentsPackagePath/Configuration/Settings.Components.yaml"
}

# add a component from the component library to the project
function add-component {
    sitePackageNamespace="MyVendor.AwesomeNeosProject"
    sitePackageVendor=$(echo "$sitePackageNamespace" | cut -d '.' -f 1)
    sitePackagePackage=$(echo "$sitePackageNamespace" | cut -d '.' -f 2)
    sitePackageNamespaceWithSlashes="${sitePackageNamespace//./\\}"
    sitePackagePath="./app/DistributionPackages/$sitePackageNamespace"

    componentsPackageNamespace="Sandstorm.ComponentLibrary"
    componentsPackageVendor=$(echo "$componentsPackageNamespace" | cut -d '.' -f 1)
    componentsPackagePackage=$(echo "$componentsPackageNamespace" | cut -d '.' -f 2)
    componentsPackageNamespaceWithSlashes="${componentsPackageNamespace//./\\}"
    componentsPackagePath="./app/DistributionPackages/$componentsPackageNamespace"

    constraintsNodeName="Constraints.Base"

    if [ -z "$1" ]; then
        # get component name from user input
        read -p "Name of the component: " name
    else
        name="$1"
    fi

    # check if component exists and break with warning if not
    yq eval ".$componentsPackageNamespace.Components | keys" "$componentsPackagePath/Configuration/Settings.Components.yaml" | grep -q "$name" || { _echo_red "Component $name not found"; _echo_yellow "Available components:"; list-components; return 1; }

    # TODO: distinguish between document and content types
    documentName="Content.$name"
    componentPath="$componentsPackagePath/$name"

    # copy files
    copy_files() {
        while read -r path; do
            fullComponentPath="$componentsPackagePath/$path"
            if [ -f "$fullComponentPath" ]; then
                cp "$fullComponentPath" "$sitePackagePath/$path"
            elif [ -d "$fullComponentPath" ]; then
                cp -r "$fullComponentPath" "$sitePackagePath/$path"
            fi
            _echo_green "Copied $fullComponentPath to $sitePackagePath/$path"

            # rename package
            find "$sitePackagePath/$path" -type f -exec grep -Iq . {} \; -print | xargs sed -i '' "s/${componentsPackageVendor}/${sitePackageVendor}/g"
            find "$sitePackagePath/$path" -type f -exec grep -Iq . {} \; -print | xargs sed -i '' "s/${componentsPackagePackage}/${sitePackagePackage}/g"
        done
    }

    yq eval ".$componentsPackageNamespace.Components.$name.files[]" "$componentsPackagePath/Configuration/Settings.Components.yaml" | copy_files

    # copy eel helper and add to configuration
    add_eel_helper() {
        while read -r path; do
            fileName="${path}Helper.php"
            fullComponentPath="$componentsPackagePath/Classes/Eel/Helper/$fileName"
            fullSitePackagePath="$sitePackagePath/Classes/Eel/Helper/$fileName"
            cp "$fullComponentPath" $fullSitePackagePath
            yq --inplace ".Neos.Fusion.defaultContext += {\"$sitePackageNamespace.$path\": \"$sitePackageNamespaceWithSlashes\\Eel\\Helper\\${path}Helper\"}" "$sitePackagePath/Configuration/Settings.yaml"
            _echo_green "Copied $fullComponentPath to $fullSitePackagePath; added constraint to Settings.yaml"

            # rename package
            find $fullSitePackagePath -type f -exec grep -Iq . {} \; -print | xargs sed -i '' "s/${componentsPackageVendor}/${sitePackageVendor}/g"
            find $fullSitePackagePath -type f -exec grep -Iq . {} \; -print | xargs sed -i '' "s/${componentsPackagePackage}/${sitePackagePackage}/g"
        done
    }

    yq eval ".$componentsPackageNamespace.Components.$name.eelHelpers[]" "$componentsPackagePath/Configuration/Settings.Components.yaml" | add_eel_helper

    # install js dependencies
    install_js_dependencies() {
        pushd "$sitePackagePath/Resources/Private" > /dev/null
            source "$HOME/.nvm/nvm.sh"
        nvm install && nvm use && yarn
        while read -r path; do
             yarn add "$path"
        done
        popd > /dev/null
    }

    yq eval ".$componentsPackageNamespace.Components.$name.jsDependencies[]" "$componentsPackagePath/Configuration/Settings.Components.yaml" | install_js_dependencies

    # add constraint to start page config
    pushd "$sitePackagePath/NodeTypes/Constraints" > /dev/null
    constraintsKey="$sitePackageNamespace:$documentName"
    if grep -q "$constraintsKey" "$constraintsNodeName.yaml"
    then
        _echo_yellow "- Constraints for $constraintsKey already set"
    else
        yq --inplace '."'$sitePackageNamespace':'$constraintsNodeName'".constraints.nodeTypes += {"'$constraintsKey'": true}' "$constraintsNodeName.yaml"
        sed -i '' "s/${constraintsKey}/'${constraintsKey}'/g" "$constraintsNodeName.yaml"
        _echo_green "Added constraints for $constraintsKey to $constraintsNodeName.yaml"
    fi
    popd > /dev/null

    _echo_green "=> Component $name added"
}
