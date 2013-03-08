require 'net/http'
require "rexml/document"
require 'rubygems'
require 'hpricot'
require 'open-uri'

def getItemsFrom(url,query)

    query = query.tr(" ","+")
    
    # Get the html page of youtube 
    youtube_html = Hpricot(open(url+query))
    
    tab = "<items>"

    # serch in the html hierarchy: h3/a
    (youtube_html/"h3/a").each { |elem|
      suggestion = elem.attributes['title']
      tab += buildXMLItemFor(suggestion,"Youtube suggest",suggestion,"yes","youtube.png")
    }

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

puts getItemsFrom('http://www.youtube.com/results?search_query=',$*[0])