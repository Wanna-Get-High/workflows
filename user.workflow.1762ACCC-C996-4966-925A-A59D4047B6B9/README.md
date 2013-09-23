# Version 1.4

## 1.0
> Basics are settled :
> > adding
> > removing 
> > jumping to finder folder

## 1.1
> Added the possibility to open a terminal at the location of the bookmark

## 1.2
> fixed the fact that the focus wasn't on it if you were in a different space

## 1.3
> added the possibility to search with more than one world

## 1.4
> added the possibility to search with capitalised letter or not and always find the item

## TODO:
> Being able to detect if a terminal is already open in the selected directory 
> > if true put the focus on this terminal (if there are several propose a list)
> > if false open a new one

# DESCRIPTION

This workflow allows you to open a folder in the Finder via bookmark.

You will be able to add bookmarks and also delete the ones you have added.

When typing the query suggestions of bookmarks are showed directly in Alfred.

It will save you a lot of time if you always jump from a folder to another :)


# INSTALLATION / REQUIREMENT

	In order to use this you will have first to install some package ( and ruby if it's not already on your machine).

1) Install macport from this website:
> http://www.macports.org/install.php
	
	Please read carefully the requirement of MacPort.
	Then download and install your suited version of MacPorts.

2) Install Bash:
> The jump command doesn't support apple bash so you need to install the one on MacPort via:
> > sudo port install bash
	
> And then follow the step 3 there:
> > https://trac.macports.org/wiki/howto/bash-completion

3) Install jump:
> Follow the instruction of the creator: 
> > https://github.com/flavio/jump
	

	If you want to install bash-completion follow the step 4)


4) Install "bash-completion" and not (bash_completion): 
> You will have to make some change to use it:
> > https://trac.macports.org/wiki/howto/bash-completion

> And then follow the rest of the instruction founded there: 
> > https://github.com/flavio/jump

5) Test it and use it :)

# USAGE OF THE WORKFLOW

j {query}


if ({query} == * )
> show all of the bookmark

if (ALT button is also pressed) 
> if (query matches one or more bookmark) 
> > it will take the path of the frontmost window and replace the current path of the selected bookmark
> 
> else 
> > It will create a new bookmark with: 
> > > the path of the frontmost window
> > > the query as name
>
> end if

else if (CTRL button is pressed)
> if (query matches one or more bookmark)
> > It will delete the currently selected bookmark
>
> else
> > It will show a notification that it couldn't be done
>
> end if

else if (SHIFT button is pressed)
> if (query matches one or more bookmark)
> > It will open a Terminal window at the path of the bookmark
>
> else 
> > It will show a notification that it couldn't be done
>
> end if

else // no button is pressed
> if (query matches one or more bookmark) 
> > Jump to the folder of the selected bookmark. 
>
> else 
> > Show a notification that this bookmark doesn't exist
>
> end if

end if


# CREDITS / COPYRIGHT

Many thanks to:

> Flavio (https://github.com/flavio/jump) which created the shell script.
>
> Benjamin a friend of mine which shows me this script (https://github.com/BenjaminVanRyseghem)


If you have some suggestions or bug report email me @ 
alfredlotl (at) gmail (dot) com