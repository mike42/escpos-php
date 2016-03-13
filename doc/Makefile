html: escpos.conf
	doxygen escpos.conf

latex: html
	# Do nothing

xml: html
	xsltproc xml/combine.xslt xml/index.xml > all.xml

clean:
	rm --preserve-root -Rf html latex xml doxygen_sqlite3.db all.xml

