#!/bin/bash
set -xe

IMAGE_NAME='cep-bookings-builder'
BUILD_ARTIFACTS_DIR='build'

sudo docker build -t $IMAGE_NAME -f Dockerfile .

rm -rf $BUILD_ARTIFACTS_DIR
mkdir -p $BUILD_ARTIFACTS_DIR
sudo docker run --volume $BUILD_ARTIFACTS_DIR:/out -it $IMAGE_NAME
sudo chown "$USER":"$GROUP" $BUILD_ARTIFACTS_DIR/cep-events-calendar-marketo.tar.gz
