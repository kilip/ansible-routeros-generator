ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))


meta:
	composer meta


verify:
	composer verify
