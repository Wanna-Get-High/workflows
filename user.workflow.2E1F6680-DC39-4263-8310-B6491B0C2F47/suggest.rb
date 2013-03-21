# don't intervert the two requirement
require 'rubygems'
require 'json'

# the xml items that will be returned
# it is filled only in the method recursiveXmlBuildFrom
$xmlItem = ""


# build the xml form recursively
def recursiveXmlBuildFrom(array, queryArray)
    
    # for each item in the array (hash)
    array.each { | item |
        
        # get the type of the current item
        type = item.fetch("type")
        
        # if the type is an url
        # add it to the xml form if the url or name include query 
        if (type.eql?"url") then
            
            name = item.fetch("name").gsub("&", "&amp;")
            lowerCaseName = name.tr("A-Z","a-z")
            url = item.fetch("url").gsub("&", "&amp;")
            
            containsAllItemQuery = true
            
            # if all of the elements of the querryArray is contained in either
            # the url or the name of the tab -> add it
            queryArray.each { | query |
                if (not (url.include?query or lowerCaseName.include?query or query.eql?"*")) then
                    containsAllItemQuery = false
                end
            }
            
            if containsAllItemQuery then
                $xmlItem += buildXMLItemWith(url,name,url,"yes","icon.png")
            end
            
        else
            # else if it's a folder 
            # recursively call the method with the children of this folder (array of hash)
            if (type.eql?"folder") then
                recursiveXmlBuildFrom(item.fetch("children"),queryArray)
            end
        end
    }
end 


# retrieve the xml form for this query
def getItemsFrom(query)

    # open google chrome bookmark file
    fic = File.open(ENV['HOME']+"/Library/Application Support/Google/Chrome/Default/Bookmarks",'r')

    # parse the file into data
    data = JSON.parse(fic.read)

    # get the root array of hash of the bookmarks
    array = data.fetch("roots").fetch("bookmark_bar").fetch("children")
    
    # fill the $xmlItem variable with the items corresponding to the query
    # the query will be splited if more than one word is used to search
    recursiveXmlBuildFrom(array,query.split(" "))

    # return the xml form to alfred
    return "<items>" + $xmlItem + "</items>"
end


# build the XML item for alfred to show it in the list bellow
def buildXMLItemWith(arg, title, subtitle, valid, icon)
    
    string = '<item uid="suggest {query}" arg=\''+arg+'\'>'
    string += '<title>'+title+'</title>'
    string += '<subtitle>'+subtitle+'</subtitle>'
    string += '<valid>'+valid+'</valid>'
    string += '<icon>'+icon+'</icon>'
    string += '</item>'
    return string
end


# the starting point
puts getItemsFrom($*[0])








