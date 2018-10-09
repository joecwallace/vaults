#!/bin/sh

# Install op - the CLI app for 1Password

wget --quiet https://cache.agilebits.com/dist/1P/op/pkg/v0.5/op_linux_386_v0.5.zip
unzip op_linux_386_v0.5.zip
gpg --keyserver hkp://pgp.mit.edu:80 --receive-keys 3FEF9748469ADBE15DA7CA80AC2D62742012EA22
gpg --verify op.sig op
sudo mv op /usr/local/bin
rm op.sig op_linux_386_v0.5.zip
