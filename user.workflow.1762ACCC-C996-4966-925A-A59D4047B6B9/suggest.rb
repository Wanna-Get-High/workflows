


# build the XML item for alfred to show it in the list bellow
def buildXMLItemFor(arg, title, subtitle, valid, icon)
    string = '<item uid="suggest {query}" arg=\''+arg+'\'>'
    string += '<title>'+title+'</title>'
    string += '<subtitle>'+subtitle+'</subtitle>'
    string += '<valid>'+valid+'</valid>'
    string += '<icon>'+icon+'</icon>'
    string += '</item>'
    return string
end


# return all of the items contained in jmpFilePath in wich query is included
def getItemsForQuery(jmpFilePath, query)
  tab =  "<items>"
  
  # split the query in an array for each word  
  queryArray = query.split(" ") 
  
  # for each line in jmpFilePath do
  File.open(jmpFilePath, "r").each_line.each { |line|
    
    index = line.index(':')
    
    if (index != nil) then
      argTitle = line[0,index]
      subtitle = "jump to: " + line[index+2..-1]

      containsAllQueryItem = true
      
      # if all of the elements of the querryArray is contained in
      # the argTitle (the title of the bookmark) -> add it
      queryArray.each { | query |
          if (not (query.eql?"*" or argTitle.include?(query))) then
              containsAllQueryItem = false
          end
      }

      if containsAllQueryItem then
        tab += buildXMLItemFor(argTitle, argTitle, subtitle, "yes",  "icon.png")
      end
      
    end
  }

  # if there is no element create on in order to pass the argument 
  # if the user want to add an item
  if (!(tab.include?("<title>") && tab.include?("<subtitle>"))) then
    tab += buildXMLItemFor(query, "bookmark not found", "press ALT to add it", "yes", "folder_add.png")
  end
  
    tab +=  "</items>"
    return tab
end

# write the xml form for alfred
puts getItemsForQuery(File.expand_path("~/.jumprc"), $*[0])









