#!/bin/bash

source ./dev_utilities.sh

# Generate a Repository
function generate-repository {
  namespace="MyVendor.AwesomeNeosProject"
  defaultName="Repository"
  defaultItemName="Item"

  generatorFolderName="_Generator"

  defaultFullName="Document.$defaultName"
  defaultItemFullName="Document.$defaultName.$defaultItemName"
  defaultListContentName="Content.$defaultName.List"

  _echo_yellow "#########################################################"
  _echo_yellow "#                                                       #"
  _echo_yellow "#                   Repository Generator                #"
  _echo_yellow "#                                                       #"
  _echo_yellow "#########################################################"

  pushd "./app/DistributionPackages/$namespace/" > /dev/null

  echo
  # USER INPUT
  read -p "Name your repository (default='$defaultName'): " name
  read -p "Name your repository items (default='$defaultItemName'): " itemName

  itemName=${itemName:-"$defaultItemName"}

  if [ "$name" ]
    then
      documentName="Document.$name"
      itemDocumentName="Document.$name.$itemName"
      ListContentName="Content.$name.List"
      siteFullName="Document.StartPage"
      constraintsMixinFullName="Constraints.Special"

      echo
      echo "These files will be created:"
      _echo_green "  * NodeTypes/Document/$documentName.yaml"
      _echo_green "  * NodeTypes/Document/$itemDocumentName.yaml"
      _echo_green "  * NodeTypes/Content/$ListContentName.yaml"
      _echo_green "  * Resources/Private/Fusion/Integration/Document/$documentName.fusion"
      _echo_green "  * Resources/Private/Fusion/Integration/Document/$itemDocumentName.fusion"
      _echo_green "  * Resources/Private/Fusion/Integration/Content/$ListContentName.fusion"
      echo
      echo "We will also add constraints to:"
      _echo_green "  * NodeTypes/Document/$siteFullName.yaml"
      _echo_green "  * NodeTypes/Constraints/$constraintsMixinFullName.yaml"
      echo
      _echo_yellow "Hit RETURN to generate, or CTRL+C to exit"
      read -p ""

      pushd "./NodeTypes/Document" > /dev/null
      echo "... Copying Document NodeType Templates"
      cp "$generatorFolderName/$defaultFullName.yaml" "$documentName.yaml"
      cp "$generatorFolderName/$defaultItemFullName.yaml" "$itemDocumentName.yaml"

      echo "... Replacing Names"

      # REPOSITORY
      # IMPORTANT: we need to replace the repository item name first
      sed -i '' "s/${defaultItemFullName}/${itemDocumentName}/g" "$documentName.yaml"
      sed -i '' "s/${defaultFullName}/${documentName}/g" "$documentName.yaml"
      # Replace Item Label
      sed -i '' "s/${defaultName}/${name}/g" "$documentName.yaml"
      # Replace NodeType in node templates
      sed -i '' "s/${defaultListContentName}/${ListContentName}/g" "$documentName.yaml"

      # ITEM
      sed -i '' "s/${defaultItemFullName}/${itemDocumentName}/g" "$itemDocumentName.yaml"
      # Replace Item Label
      sed -i '' "s/${defaultName} ${defaultItemName}/${name} ${itemName}/g" "$itemDocumentName.yaml"

      echo "... Updating Constraints in $siteFullName.yaml"
      constraintsKey="$namespace:$documentName"
      if grep -q "$constraintsKey" "$siteFullName.yaml"
        then
          _echo_yellow "    Constraints for $constraintsKey already set"
        else
          yq --inplace '."'$namespace':'$siteFullName'".constraints.nodeTypes += {"'$constraintsKey'": true}' "$siteFullName.yaml"
          sed -i '' "s/${constraintsKey}/'${constraintsKey}'/g" "$siteFullName.yaml"
      fi
      popd > /dev/null
      echo
      pushd "./Resources/Private/Fusion/Integration/Document" > /dev/null
      echo "... Copying Document Fusion File Templates"
      cp "$generatorFolderName/$defaultFullName.fusion" "$documentName.fusion"
      cp "$generatorFolderName/$defaultItemFullName.fusion" "$itemDocumentName.fusion"

      echo "... Replacing Names"
      sed -i '' "s/${defaultFullName}/${documentName}/g" "$documentName.fusion"
      sed -i '' "s/${defaultItemFullName}/${itemDocumentName}/g" "$itemDocumentName.fusion"
      popd > /dev/null
      echo
      pushd "./NodeTypes/Content" > /dev/null
      echo "... Copying Content NodeType Templates"
      cp "$generatorFolderName/$defaultListContentName.yaml" "$ListContentName.yaml"
      echo "... Replacing Name"
      sed -i '' "s/${defaultListContentName}/${ListContentName}/g" "$ListContentName.yaml"
      sed -i '' "s/${defaultName} List/${name} List/g" "$ListContentName.yaml"
      popd > /dev/null

      pushd "./NodeTypes/Constraints" > /dev/null
      echo "... Updating Constraints in $constraintsMixinFullName.yaml"
      constraintsKey="$namespace:$ListContentName"
      if grep -q "$constraintsKey" "$constraintsMixinFullName.yaml"
        then
          _echo_yellow "    Constraints for $constraintsKey already set"
        else
          yq --inplace '."'$namespace':'$constraintsMixinFullName'".constraints.nodeTypes += {"'$constraintsKey'": true}' "$constraintsMixinFullName.yaml"
          sed -i '' "s/${constraintsKey}/'${constraintsKey}'/g" "$constraintsMixinFullName.yaml"
      fi
      popd > /dev/null
      echo
      pushd "./Resources/Private/Fusion/Integration/Content" > /dev/null
      echo "... Copying Content Fusion File Templates"
      cp "$generatorFolderName/$defaultListContentName.fusion" "$ListContentName.fusion"
      echo "... Replacing Names"

      # Replacing Prototype
      sed -i '' "s/${defaultListContentName}/${ListContentName}/g" "$ListContentName.fusion"

      # Replacing referenced NodeTypes
      # IMPORTANT: we need to replace the repository item name first
      sed -i '' "s/${defaultItemFullName}/${itemDocumentName}/g" "$ListContentName.fusion"
      sed -i '' "s/${defaultFullName}/${documentName}/g" "$ListContentName.fusion"
      popd > /dev/null
    else
      _echo_yellow "Nothing to do :("
  fi
  popd > /dev/null

  echo
  _echo_green "All done, have fun ;)"
}

