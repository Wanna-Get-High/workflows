require 'net/http'
require "rexml/document"


def getItemsFrom(url,query)
    
    query = query.tr(" ","+")

    xml_data = Net::HTTP.get_response(URI.parse(url+query)).body
    doc = REXML::Document.new(xml_data)
   
    tab = "<items>"

    doc.elements.each("toplevel/CompleteSuggestion/suggestion") { |elem |
      suggestion = elem.attributes["data"]
      tab += buildXMLItemFor(suggestion,"Google suggest",suggestion,"yes","magnifier.png")
    }
    
    # if there is no element create on in order to pass the argument 
    # if the user want to add an item
    if (!(tab.include?("<title>") && tab.include?("<subtitle>"))) then
      buildXMLItemFor(query,"No suggestion just press enter :)",query,"yes","magnifier.png")
    end

    tab += "</items>"
    return tab
end

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


puts getItemsFrom('http://google.com/complete/search?output=toolbar&q=',$*[0])