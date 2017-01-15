#!/bin/sh
# packpack setup file for the ZoneMinder project
# Written by Andrew Bauer

# Check to see if this script has access to all the commands it needs
for CMD in set echo curl repoquery git ln mkdir patch; do
  type $CMD &> /dev/null

  if [ $? -ne 0 ]; then
    echo
    echo "ERROR: The script cannot find the required command \"${CMD}\"."
    echo
    exit -1
  fi
done

# Verify OS & DIST environment variables have been set before calling this script
if [ -z "${OS}" ] || [ -z "${DIST}" ]; then
    echo "ERROR: both OS and DIST environment variables must be set"
    exit -1
fi

# Steps common to all builds
mkdir -p build
if [ -e "packpack/Makefile" ]; then
    echo "Checking packpack github repo for changes..."
    git -C packpack pull origin master
else
    echo "Cloning pakcpack github repo..."
    git clone https://github.com/packpack/packpack.git packpack
fi

# Steps common to Redhat distros
if [ "${OS}" == "el" ] || [ "${OS}" == "fedora" ]; then
    CRUDVER="3.0.10"
    echo "Retrieving Crud submodule..."
    curl -L https://github.com/FriendsOfCake/crud/archive/v${CRUDVER}.tar.gz > build/crud-${CRUDVER}.tar.gz

    if [ $? -ne 0 ]; then
        echo "ERROR: Crud tarball retreival failed..."
        exit -1
    fi

    # %autosetup support has been merged upstream. No need to patch
    #patch -p1 < utils/packpack/autosetup.patch
    ln -sf distros/redhat rpm

    if [ "${OS}" == "el" ]; then
        zmrepodistro=${OS}
    else
        zmrepodistro="f"
    fi

    # Let repoquery determine the full url and filename of the zmrepo rpm we are interested in
    result=`repoquery --repofrompath=zmpackpack,https://zmrepo.zoneminder.com/${zmrepodistro}/${DIST}/x86_64/ --repoid=zmpackpack --qf="%{location}" zmrepo 2> /dev/null`

    if [ -n "$result" ] && [ $? -eq 0  ]; then
        echo "Retrieving ZMREPO rpm..."
        curl $result > build/zmrepo.noarch.rpm
    else
        echo "ERROR: Failed to retrieve zmrepo rpm..."
        echo -1
    fi

    echo "Starting packpack..."
    packpack/packpack -f utils/packpack/redhat_package.mk redhat_package

# Steps common the Debian based distros
elif [ "${OS}" == "debian" ] || [ "${OS}" == "ubuntu" ]; then

    echo Do some stuff here

fi


