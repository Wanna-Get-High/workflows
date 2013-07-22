


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
def getTabsToOpenOrClose()
    xml_tab =  "<items>"  
    
    # Create a list of tab to be oppenned (add or remove some if you want to)
    # leave a space after each URL so that it could be parsed after 
    # but not on the last one of the list or it will open google.fr
    
    # if there is a & escape it with --> &amp; <--
    tab_to_open = "http://127.0.0.1:8080/sabnzbd/ "
    tab_to_open += "http://www.binnews.in "
    tab_to_open += "http://www.binsearch.info/ "
    tab_to_open += "http://fr.tvsubtitles.net/"

    xml_tab += buildXMLItemFor(tab_to_open, "Will","Open SABnzbd, binnews, binsearch, tvsubtitles", "yes",  "icon.png")
    xml_tab +=  "</items>"
    
    return xml_tab
end


# write the xml form for alfred
puts getTabsToOpenOrClose()









