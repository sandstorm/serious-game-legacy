#!/usr/bin/env bash

_green_echo() {
  printf "\033[0;32m%s\033[0m\n" "${1}"
}
_yellow_echo() {
  printf "\033[1;33m%s\033[0m\n" "${1}"
}
_red_echo() {
  printf "\033[0;31m%s\033[0m\n" "${1}"
}
_grey_echo() {
  printf "\033[0;37m%s\033[0m\n" "${1}"
}

_red_echo "TODO - FIX ME"
exit 1

############# Backing up .git #############

# for easier development of this file
if [ "$1" = "--restore-git" ]
  then
    if [ -d ".git_back" ]
      then
        _yellow_echo "Found folder .git_back, restoring ..."
        rm -rf .git
        mv .git_back .git
      else
        _red_echo "No folder .git_back present, nothing to restore."
    fi
    exit 0
fi

################ Defaults #################

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

############### Preparations ################

_yellow_echo "Removing Containers ..."
docker compose down

############### Removing Kickstart Infos and Files ################

_yellow_echo "Removing kickstart infos and files ..."
echo "$(sed '/KICKSTART_INFO_SECTION__START/,/KICKSTART_INFO_SECTION__END/d' README.md)" > ./README.md

if [ "$1" != "--dev" ]
  then
    rm ./kickstart.sh
fi
echo

############## Cleanup docker-compose.yml ################

_yellow_echo "Cleanup docker-compose.yml ..."
echo "$(sed '/start: delete on kickstart/,/end: delete on kickstart/d' docker-compose.yml)" > docker-compose.yml

############## Cleanup gitlab-ci ################

_yellow_echo "Cleanup .gitlab-ci.yml ..."
echo "$(sed '/start: delete on kickstart/,/end: delete on kickstart/d' ci/common.gitlab-ci.yml)" > ci/common.gitlab-ci.yml
echo "$(sed '/start: delete on kickstart/,/end: delete on kickstart/d' ci/staging.gitlab-ci.yml)" > ci/staging.gitlab-ci.yml

############### Initializing new Git ################

initNewGitRepo="no"
_yellow_echo "The kickstart has finished. Should we init a new git repository for you?"
_red_echo "This will remove the .git folder and run 'git init'!!!"
_red_echo "If you are unsure you can do it later manually."
read -p "Init new repo 'yes/no' (default=$initNewGitRepo): " initNewGitRepo
initNewGitRepo=${initNewGitRepo:-"no"}
echo

if [ "$initNewGitRepo" = "yes" ]
  then
    _yellow_echo "Backing up .git"
    mv .git .git_back
    rm -rf .git

    git init -b main

    _yellow_echo "Please provide the SSH url to your repo. If you have not created a repo yet"
    _yellow_echo "please do it now ;)"
    read -p "Repo Url (ssh://git@...): " repoUrl

    if [ $repoUrl ]
      # we have move the README to prevent conflicts with an initialized git repo
      # which usually has an empty README
      mv ./README.md ./README_PROJECT.md
      then
        if [[ $repoUrl == *"gitlab.sandstorm.de"* ]]; then
          _yellow_echo "Sandstorm Gitlab repo detected ;)"
          repoPath=$(echo $repoUrl | sed -e 's;ssh://git@gitlab.sandstorm.de:29418/;;g' -e 's;.git;;g')
          _yellow_echo "Replacing path to dockerhub in app.yaml with $repoPath"
          sed -i '' "s;${defaultDockerHubPath};${repoPath};g" deployment/staging/app.yaml

          ############### Creating Kubernetes Namespace ################

          kubernetesNamespace="$vendorNameLowerCase-$packageNameLowerCase-staging"
          # 1) find the right kubernetes pod to connect to
          kubectl >> /dev/null 2>&1
          if [[ $? -gt 0 ]]; then
            echo "kubectl must be installed to run the script"
            echo "Not Skipping. Please create the namespace manually"
          else
            kubectl get namespace "$kubernetesNamespace" >> /dev/null 2>&1
            if [[ $? -gt 0 ]]; then
                echo "Kubernetes namespace '$kubernetesNamespace' not found!"
                read -p "Create new kubernetes namespace 'yes/no' (default=$createKubernetesNamespace): " createKubernetesNamespace
                createKubernetesNamespace=${createKubernetesNamespace:-"yes"}

                if [[ "$createKubernetesNamespace" = "yes" ]]; then
                  read -p "Comma separated list of maintainers (for namespace description): " maintainerNames

cat <<EOF | kubectl create -f -
apiVersion: v1
kind: Namespace
metadata:
  name: $kubernetesNamespace
  annotations:
    # IMPORTANT: the projectId points to "apps" and needs to be changed for major updates of the cluster
    field.cattle.io/projectId: "c-m-vlgfn55h:p-h5lr5"
    field.cattle.io/description: |-
      Maintainers: $maintainerNames;
      Repo: https://gitlab.sandstorm.de/$repoPath
EOF
                fi
            else
              echo "Namespace '$kubernetesNamespace' found ;)"
            fi
          fi
        fi

        git add .
        git commit -m "TASK: Neos Kickstart"

        git remote add origin $repoUrl
        echo "Touch your YubiKey"
        git fetch
        git branch --set-upstream-to=origin/main main
        git pull --rebase
        if [ -f "README.md" ]
          then
            mv README.md README_CONFLICT.md
        fi
        # move back project documentation README template provided with the kickstarter package ;)
        mv README_PROJECT.md README.md
        git add .
        git commit -m "TASK: Fixed possible README conflict"
        git push
    fi
  else
    _yellow_echo "NO git repository was initialized."
fi
echo

echo
_green_echo "KICKSTART has finished successfully ;)"
_green_echo "run 'dev start' to start the docker container"
_green_echo "To add new components use 'dev add-component' or 'dev list-components' to see available components"


