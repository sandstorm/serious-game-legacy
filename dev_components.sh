#!/bin/bash

source ./dev_utilities.sh

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

    # move node type config
    mv "$componentsPackagePath/NodeTypes/Content/$documentName.yaml" "$sitePackagePath/NodeTypes/Content/$documentName.yaml"
    # move integrational component
    mv "$componentsPackagePath/Resources/Private/Fusion/Integration/Content/$documentName.fusion" "$sitePackagePath/Resources/Private/Fusion/Integration/Content/$documentName.fusion"
    # move presentational component dir
    mv "$componentsPackagePath/Resources/Private/Fusion/Presentation/Components/$name" "$sitePackagePath/Resources/Private/Fusion/Presentation/Components/$name"
    # move eel helper and add to configuration
    # TODO
    # move translation files
    # TODO
    # move tests
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
