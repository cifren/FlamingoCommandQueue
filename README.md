Flamingo Command Queue
======================

[![Coverage Status](https://coveralls.io/repos/github/cifren/FlamingoCommandQueue/badge.svg?branch=master)](https://coveralls.io/github/cifren/FlamingoCommandQueue?branch=master)

[![Build Status](https://travis-ci.org/cifren/FlamingoCommandQueue.svg?branch=master)](https://travis-ci.org/cifren/FlamingoCommandQueue)

Overview
========

This Symfony2 bundle aims to provide classes to build you own command management

The idea is:

1. Use CommandManager in your command Classes (start and stop function)
2. Declare its name and group
3. The tool will manage queuing instance for you, based on the group or/and uniqueId
4. Watch performance / log on screen (require to create the interface or use AdminSonata for fast implementation)


Documentation
=============

For installation and how to use the bundle refer to [Resources/doc/index.md](Resources/doc/index.md)
