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
    sitePackagePath="./app/DistributionPackages/$sitePackageNamespace"
    componentsPackageNamespace="Sandstorm.ComponentLibrary"
    componentsPackagePath="./app/DistributionPackages/$componentsPackageNamespace"
    constraintsNodeName="Constraints.Base"

    # get component name from user input
    read -p "Name of the component: " name

    # TODO: check if component exists and break with warning if not

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
        done
    }

    yq eval ".$componentsPackageNamespace.Components.$name.files[]" "$componentsPackagePath/Configuration/Settings.Components.yaml" | copy_files

    # copy eel helper and add to configuration
    # TODO

    # add constraint to start page config
    pushd "$sitePackagePath/NodeTypes/Constraints" > /dev/null
    constraintsKey="$sitePackageNamespace:$documentName"
    if grep -q "$constraintsKey" "$constraintsNodeName.yaml"
    then
        _echo_yellow "- Constraints for $constraintsKey already set"
    else
        yq --inplace '."'$sitePackageNamespace':'$constraintsNodeName'".constraints.nodeTypes += {"'$constraintsKey'": true}' "$constraintsNodeName.yaml"
        sed -i '' "s/${constraintsKey}/'${constraintsKey}'/g" "$constraintsNodeName.yaml"
    fi
    popd > /dev/null

    # rename package in all added files
    # TODO

    _echo_green "Component $name added"
}
