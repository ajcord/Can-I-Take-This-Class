# Can I Take This Class?

_Find out whether you'll get into the classes you want at UIUC._

## Overview
Can I Take This Class is a tool for students at the University of Illinois at Urbana-Champaign to predict their chances of getting into the classes they want. It uses historical course registration data to predict when classes will fill up and when they will open up again.

It was developed by Alex Cordonnier as the successor to ClassMaster, a CS 411 final project developed with Clarence Elliott, Gaurang Jain, and Sean Mulroe.

## How it works
First, it calculates the percentage of sections that were open in previous semesters around the equivalent registration date. More recent semesters are weighted more heavily because classes and demand change over time.

Next, it calculates the percentage of sections that historically open up later during registration or even after classes start.

Finally, assuming you need to get into one of each type of section, your chances of getting into a class are as good as the lowest section type's chances.

## What it doesn't do
Can I Take This Class works well for most classes, but there are a few things it can't do:

* Predict classes that haven't been offered before or were last offered before Fall 2015
* Predict specific sections, like if you really want the 11 AM lecture and not the 8 AM one
* Figure out restrictions on a section or course. Restricted courses are treated as if they are open.
* Register for you or tell you when a class opens up

It works best on classes where you need one of every type of section. For other classes, you can use the table provided under the prediction to see what your chances would really be.

## For developers
Want to use these predictions in your own project? Check out the [API](https://github.com/ajcord/Can-I-Take-This-Class/wiki/API-Docs)!
