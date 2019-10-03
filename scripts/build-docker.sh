#!/bin/bash
set -xe

IMAGE_NAME='cep-venues-assets-builder'
BUILD_ARTIFACTS_DIR='build'

sudo docker build -t $IMAGE_NAME -f Dockerfile .
containerID=$(sudo docker run -dt $IMAGE_NAME)

mkdir -p $BUILD_ARTIFACTS_DIR
sudo docker cp $containerID:/opt/build/build/cep-events-calendar-marketo.tar.gz $BUILD_ARTIFACTS_DIR/.
sudo chown $USER:$GROUP $BUILD_ARTIFACTS_DIR/cep-events-calendar-marketo.tar.gz
sleep 1
sudo docker rm -f $containerID
