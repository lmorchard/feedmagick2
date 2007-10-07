#!/bin/sh
# TODO: Make this suck less, because it sucks a lot.
EXTRACT_LN=`grep -Hn "### Extracted" TODO | cut -d: -f2`
cp TODO TODO-bak
head -n $EXTRACT_LN TODO-bak > TODO
echo >> TODO
echo '<pre class="todo">' >> TODO
find . -type f -not -path '*/extlib/*' -not -path '*/xfers/*' -not -path '*/data/*' -not -path '*/docs/*' \( -path '*htdocs*' -o -name '*.php' -o -name '*.sh' -o -name '*.xsl' -o -name '*.cgi' -o -name '*.txt' -o -name '*.py' -o -name '*.js' -o -name '*.xsl' -o -name '*.html' \) -exec grep -Hni '@todo ' {} \; | grep -v 'find -type f' >> TODO
find . -type f -not -path '*/extlib/*' -not -path '*/xfers/*' -not -path '*/data/*' -not -path '*/docs/*'  \( -path '*htdocs*' -o -name '*.php' -o -name '*.sh' -o -name '*.xsl' -o -name '*.cgi' -o -name '*.txt' -o -name '*.py' -o -name '*.js' -o -name '*.xsl' -o -name '*.html' \) -exec grep -Hn 'TODO: ' {} \; | grep -v 'find -type f' >> TODO
echo '</pre>' >> TODO
#rm TODO-bak
