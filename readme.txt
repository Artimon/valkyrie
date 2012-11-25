 _   __     ____            _
| | / /__ _/ / /____ ______(_)__
| |/ / _ `/ /  '_/ // / __/ / -_)
|___/\_,_/_/_/\_\\_, /_/ /_/\__/
                /___/

/******************************************************************
 * Valkyrie - Php script "precompiling" autoloader.
 *****************************************************************/


## What is Valkyrie?
Valkyrie is an autoloader that collects loaded php classes to put them
together in script groups. It provides an automated "build" process to
reduce the amount of source files to load.


## How does Valkyrie work?
In the first step, the autoloader loads the file for the given script
group, which can be a route for example. Each time, during execution,
when another class has to be loaded, it will be appended to that file.
Thus, after a few requests, that file contains all necessary classes to
process that route and the autoloader itself has nothing to do any more,
while an optimized code collection file is loaded right from the start.


## Where does Valkyrie come from?
Valkyrie has been developed for the browsergame "Schlacht um Kyoto"
(engl.: "Battle for Kyoto) at http://www.schlacht-um-kyoto.de in order
to reduce a high amount of class files down to one class collection
file for each route, containing only the code which is needed for
processing that route.