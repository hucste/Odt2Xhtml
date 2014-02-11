﻿<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
	xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" 
	xmlns:config="urn:oasis:names:tc:opendocument:xmlns:config:1.0" 
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" 
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" 
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" 
	xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0" 
	xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" 
	xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" 
	xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" 
	xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" 
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" 
	xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" 
	xmlns:anim="urn:oasis:names:tc:opendocument:xmlns:animation:1.0" 
	xmlns:dc="http://purl.org/dc/elements/1.1/" 
	xmlns:xlink="http://www.w3.org/1999/xlink" 
	xmlns:math="http://www.w3.org/1998/Math/MathML" 
	xmlns:xforms="http://www.w3.org/2002/xforms" 
	xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" 
	xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" 
	xmlns:smil="urn:oasis:names:tc:opendocument:xmlns:smil-compatible:1.0" 
	xmlns:ooo="http://openoffice.org/2004/office" 
	xmlns:ooow="http://openoffice.org/2004/writer" 
	xmlns:oooc="http://openoffice.org/2004/calc" 
	xmlns:int="http://opendocumentfellowship.org/internal" 
	xmlns="http://www.w3.org/1999/xhtml" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	exclude-result-prefixes="office meta config text table draw presentation dr3d chart form script style number anim dc xlink math xforms fo svg smil ooo ooow oooc int #default">
	
	<xsl:output 
	method="xml" 
	indent="yes" 
	omit-xml-declaration="yes" 
	encoding="UTF-8" 
	standalone="no"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
	/>
	
<!-- declare variables -->
  <!-- build linebreak -->
	<xsl:variable name="linebreak"><xsl:text>&#xA;</xsl:text></xsl:variable>
  <!-- preserve spaces -->
	<xsl:variable name="spaces" xml:space="preserve"/>
	<!-- buid tab -->
	<xsl:variable name="tab"><xsl:text>&#x09;</xsl:text></xsl:variable>

<!-- First template -->
	<xsl:template match="/office:document">
		<xsl:element name="html">
			<xsl:attribute name="xml:lang"><xsl:value-of select="substring(office:document-meta/office:meta/dc:language,1,2)"/></xsl:attribute>
			<xsl:attribute name="lang"><xsl:value-of select="substring(office:document-meta/office:meta/dc:language,1,2)"/></xsl:attribute>
			<!-- make element head -->
			<xsl:element name="head">
			  <!-- make elements meta -->
				<xsl:apply-templates select="office:document-meta"/>
				<!-- make define CSS -->
				<xsl:call-template name="process-all-styles"/>
				<!-- make elements link (for icon) -->
				<xsl:element name="link">
					<xsl:attribute name="rel"><xsl:text>shortcut icon</xsl:text></xsl:attribute>
					<xsl:attribute name="href"><xsl:text>img/favicon.ico</xsl:text></xsl:attribute>					
					<xsl:attribute name="type"><xsl:text>image/vnd.microsoft.icon</xsl:text></xsl:attribute>
				</xsl:element>
				<xsl:element name="link">
					<xsl:attribute name="rel"><xsl:text>icon</xsl:text></xsl:attribute>
					<xsl:attribute name="href"><xsl:text>img/icone.png</xsl:text></xsl:attribute>					
					<xsl:attribute name="type"><xsl:text>image/png</xsl:text></xsl:attribute>
				</xsl:element>
			</xsl:element>
			<!-- make element body -->
			<xsl:apply-templates select="office:document-content"/>
		</xsl:element>
	</xsl:template>
	
<!-- Template 2d level -->
	<!-- 2d level: manage office:document-meta -->
	<xsl:template match="office:document-meta">
		<xsl:apply-templates/>
	</xsl:template>
	
	<!-- 2d level: procede all styles -->
	<xsl:template name="process-all-styles">
		<xsl:element name="style">
			<xsl:attribute name="type"><xsl:text>text/css</xsl:text></xsl:attribute>
			<xsl:text>
			/* ODF paragraphs, by default, don't have any line spacing. */
			p { margin: 0px; padding: 0px; }
			/* put a default link style in, so we can see links */
			a[href] { color: blue; text-decoration: underline; }
			</xsl:text>
			<!-- manage office document styles -->
			<xsl:apply-templates select="office:document-styles/office:styles"/>
			<xsl:apply-templates select="office:document-styles/office:automatic-styles"/>
			<xsl:apply-templates select="office:document-content/office:automatic-styles"/>
			<!-- manage table-of-content styles -->
			<xsl:call-template name="toc-styles"/>
		</xsl:element>
	</xsl:template>
	
	<!-- 2 level: body -->
	<xsl:template match="office:document-content">
		<xsl:element name="body">
			<xsl:comment> office:body/office:text </xsl:comment>
			<xsl:apply-templates select="office:body/office:text"/>
			<xsl:comment> add-footnote-bodies </xsl:comment>
			<xsl:call-template name="add-footnote-bodies"/>
		</xsl:element>
	</xsl:template>
	
<!-- 3rd level -->
  <!-- 3d level: element meta -->
	<xsl:template match="office:meta">
		<xsl:element name="link"> <!-- link to declarate Dublin Core -->
			<xsl:attribute name="rel"><xsl:text>schema.DC</xsl:text></xsl:attribute>
			<xsl:attribute name="href"><xsl:text>http://purl.org/dc/elements/1.1/</xsl:text></xsl:attribute>
		</xsl:element>
		<xsl:comment> Metadata starts </xsl:comment>
		<xsl:apply-templates select="meta:generator"/>
		<xsl:apply-templates select="dc:title"/>
		<xsl:apply-templates select="dc:description"/>
		<xsl:apply-templates select="dc:subject"/>
		<xsl:apply-templates select="meta:keyword"/>
		<xsl:apply-templates select="meta:initial-creator"/>
		<xsl:apply-templates select="dc:creator"/>
		<xsl:apply-templates select="meta:creation-date"/>
		<xsl:apply-templates select="dc:date"/>
		<xsl:apply-templates select="dc:language"/>
		<xsl:element name="meta">
			<xsl:attribute name="http-equiv"><xsl:text>Content-Type</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:text>text/html;charset=UTF-8</xsl:text></xsl:attribute>
		</xsl:element>
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>DC.Format</xsl:text></xsl:attribute>
			<xsl:attribute name="scheme"><xsl:text>IMT</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:text>charset=UTF-8</xsl:text></xsl:attribute>
		</xsl:element>
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>DC.Format</xsl:text></xsl:attribute>
			<xsl:attribute name="scheme"><xsl:text>IMT</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:text>text/html</xsl:text></xsl:attribute>
		</xsl:element>
		<xsl:comment> Metadata ends </xsl:comment>
	</xsl:template>
	
	<!-- 3d level: styles -->
	<xsl:template match="office:document-styles/office:styles">
		/* Document styles : start */
		  <xsl:apply-templates/>
		/* Document styles : end */
	</xsl:template>

	<xsl:template match="office:document-styles/office:automatic-styles">
		/* Document styles : automatic styles :: start */
		<xsl:apply-templates/>
		/* Document styles : automatic styles :: end */
	</xsl:template>
	
	<xsl:template match="office:document-content/office:automatic-styles">
		/* Document content : Automatic styles :: start */
		<xsl:apply-templates/>
		/* Document content : Automatic styles :: end */
	</xsl:template>
	
	<xsl:template name="toc-styles">
		<xsl:apply-templates select="//text:table-of-content" mode="toc-styles"/>
	</xsl:template>
	<!-- end 3d level : styles -->

<!-- 4 level -->
<!-- 4 level : manage elements meta -->
  <!-- 4d lvl: dc creator -->
	<xsl:template match="dc:creator">
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>DC.Contributor</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
	</xsl:template>
	
	<!-- 4d lvl: dc date -->
	<xsl:template match="dc:date">
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>revised</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>DC.Date.modified</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
	</xsl:template>

  <!-- 4d lvl: dc description -->
	<xsl:template match="dc:description">
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>Description</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>DC.Description</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
	</xsl:template>
	
	<!-- 4d lvl: dc language -->
	<xsl:template match="dc:language">
		<xsl:element name="meta">
			<xsl:attribute name="http-equiv"><xsl:text>content-language</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>DC.Language</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
	</xsl:template>
	
  <!-- 4d lvl: dc subject -->
	<xsl:template match="dc:subject">
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>DC.Subject</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
	</xsl:template>
	
	<!-- 4d lvl: dc title -->
	<xsl:template match="dc:title">
		<xsl:element name="title"><xsl:apply-templates/></xsl:element>
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>DC.Title</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
	</xsl:template>
	
	<!-- 4d lvl: meta creation date -->
	<xsl:template match="meta:creation-date">
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>DC.Date.created</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>DC.Date.dateCopyrighted</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
	</xsl:template>
	
	<!-- 4d lvl: meta generator -->	
	<xsl:template match="meta:generator">
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>generator</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
	</xsl:template>
	
  <!-- 4d lvl: meta initial creator -->
	<xsl:template match="meta:initial-creator">
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>author</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>DC.Creator</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
	</xsl:template>
	
  <!-- 4d lvl: meta keyword -->
	<xsl:template match="meta:keyword">
		<xsl:element name="meta">
			<xsl:attribute name="name"><xsl:text>keywords</xsl:text></xsl:attribute>
			<xsl:attribute name="content"><xsl:value-of select="current()"/></xsl:attribute>
		</xsl:element>
	</xsl:template>
  <!-- end level 4 : meta -->

<!-- 4d lvl : manage body -->
  <!-- 4d lvl: element a -->
	<xsl:template match="text:a">
		<xsl:element name="a">
			<xsl:attribute name="href">
				<xsl:value-of select="@xlink:href"/>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	
	<!-- 4d lvl: preserve reference :: manage abbreviation -->
	<xsl:template match="text:reference-mark">
		<xsl:element name="abbr">
			<xsl:attribute name="title"><xsl:value-of select="@text:name"/></xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	
	<!-- 4d lvl: element br -->
	<xsl:template match="text:line-break">
		<xsl:element name="br"/>
	</xsl:template>
	
  <!-- 4d lvl: element h -->
	<xsl:template match="text:h">
	<!-- Heading levels go only to 6 in XHTML -->
	<xsl:if test="node()">
		<xsl:variable name="level">
    		<xsl:choose>
			<!-- text:outline-level is optional, default is 1 -->
				<xsl:when test="not(@text:outline-level)">1</xsl:when>
				<xsl:when test="@text:outline-level &gt; 6">6</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="@text:outline-level"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:element name="{concat('h', $level)}">
    		<xsl:attribute name="class">
				<xsl:value-of select="translate(@text:style-name,'.','_')"/>
    		</xsl:attribute>
			<xsl:element name="a">
				<xsl:attribute name="name">
					<xsl:value-of select="generate-id()"/>
				</xsl:attribute>
			</xsl:element>
    		<xsl:apply-templates/>
		</xsl:element>
	</xsl:if>
	</xsl:template>
	
	<xsl:template match="draw:frame/draw:image">
		<xsl:element name="img">
    	<!-- Default behaviour
    		<xsl:attribute name="style">
			    width: 100%;
			    height: 100%;
			    <xsl:if test="not(../@text:anchor-type='character')">
        	  display: block;
			    </xsl:if>
			  </xsl:attribute>
		  -->
			<xsl:if test="not(../@text:anchor-type='character')">
				<xsl:attribute name="style">display: block;</xsl:attribute>
			</xsl:if>
			
			<xsl:attribute name="width">
				<xsl:value-of select="substring-before(../@svg:width,'px')"/>
			</xsl:attribute>
			
			<xsl:attribute name="height">
				<xsl:value-of select="substring-before(../@svg:height,'px')"/>
			</xsl:attribute>
		
			<xsl:attribute name="alt">
				<!--<xsl:value-of select="../svg:desc"/>-->
				<xsl:value-of select="../@draw:name"/>
			</xsl:attribute>
			
			<xsl:attribute name="src">
				<xsl:value-of select="concat($param_baseuri,@xlink:href)"/>
				<!--<xsl:value-of select="@xlink:href"/>-->
			</xsl:attribute>
			
			<xsl:choose>
				<xsl:when test="../svg:desc">	<!-- office version 1.0 -->
					<xsl:attribute name="longdesc"><xsl:value-of select="../svg:desc"/></xsl:attribute>
				</xsl:when>
				<xsl:when test="../svg:title">	<!-- office version 1.2 -->
					<xsl:attribute name="longdesc"><xsl:value-of select="../svg:title"/></xsl:attribute>
				</xsl:when>
			</xsl:choose>
		</xsl:element>
	</xsl:template>
	
	<!-- 4d lvl: element li -->
	<xsl:template match="text:list-item">
		<xsl:element name="li"><xsl:apply-templates/></xsl:element>
	</xsl:template>
	
	<xsl:template match="text:list-level-style-bullet">
		<xsl:text>.</xsl:text>
		<xsl:value-of select="../@style:name"/>
		<xsl:text>_</xsl:text>
		<xsl:value-of select="@text:level"/>
		<xsl:text>{ list-style-type: </xsl:text>
		<xsl:choose>
			<xsl:when test="@text:level mod 3 = 1">disc</xsl:when>
			<xsl:when test="@text:level mod 3 = 2">circle</xsl:when>
			<xsl:when test="@text:level mod 3 = 0">square</xsl:when>
			<xsl:otherwise>decimal</xsl:otherwise>
		</xsl:choose>
		<xsl:text>;}&#xa;</xsl:text>
		<xsl:value-of select="$linebreak"/>
	</xsl:template>
	
  <xsl:key name="listTypes" match="text:list-style" use="@style:name"/>
	
	<xsl:template match="text:list">
		<xsl:variable name="level" select="count(ancestor::text:list)+1"/>
		<!-- the list class is the @text:style-name of the outermost <text:list> element -->
		<xsl:variable name="listClass">
			<xsl:choose>
				<xsl:when test="$level=1">
					<xsl:value-of select="@text:style-name"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="ancestor::text:list[last()]/@text:style-name"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<!-- Now select the <text:list-level-style-foo> element at this level of nesting for this list -->
	  <xsl:variable name="node" select="key('listTypes',$listClass)/*[@text:level='$level']"/>
		
	<!-- emit appropriate list type -->
		<xsl:choose>
		<!-- element ol -->
			<xsl:when test="local-name($node)='list-level-style-number'">
				<xsl:element name="ol">
					<xsl:attribute name="class">
						<xsl:value-of select="concat($listClass,'_',$level)"/>
					</xsl:attribute>
					<xsl:apply-templates/>
				</xsl:element>
			</xsl:when>
		<!-- element ul -->
			<xsl:otherwise>
				<xsl:element name="ul">
					<xsl:attribute name="class">
						<xsl:value-of select="concat($listClass,'_',$level)"/>
					</xsl:attribute>
					<xsl:apply-templates/>
				</xsl:element>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

  <!-- 4d lvl: element ol -->
	<xsl:template match="text:list-level-style-number">
		<xsl:text>.</xsl:text>
		<xsl:value-of select="../@style:name"/>
		<xsl:text>_</xsl:text>
		<xsl:value-of select="@text:level"/>
		<xsl:text>{ list-style-type: </xsl:text>
		<xsl:choose>
			<xsl:when test="@style:num-format='1'">decimal</xsl:when>
			<xsl:when test="@style:num-format='I'">upper-roman</xsl:when>
			<xsl:when test="@style:num-format='i'">lower-roman</xsl:when>
			<xsl:when test="@style:num-format='A'">upper-alpha</xsl:when>
			<xsl:when test="@style:num-format='a'">lower-alpha</xsl:when>
			<xsl:otherwise>decimal</xsl:otherwise>
		</xsl:choose>
		<xsl:text>;}&#xa;</xsl:text>
		<xsl:value-of select="$linebreak"/>
	</xsl:template>
	
  <!-- 4d lvl: element p -->
	<xsl:template match="text:p">
		<xsl:choose>
			<xsl:when test="descendant::draw:*">
				<xsl:apply-templates/>
				<xsl:if test="count(node())=0"><xsl:element name="br"/></xsl:if>
			</xsl:when>
			
			<xsl:when test="@text:style-name='Quotations' and node()">
				<xsl:element name="blockquote">
					<xsl:element name="p">
						<xsl:attribute name="class">
							<xsl:value-of select="translate(@text:style-name,'.','_')"/>
						</xsl:attribute>
						<xsl:apply-templates/>
					</xsl:element>
				</xsl:element>
			</xsl:when>
			
			<xsl:otherwise>
				<xsl:element name="p">
					<xsl:attribute name="class">
						<xsl:value-of select="translate(@text:style-name,'.','_')"/>
					</xsl:attribute>
					<xsl:apply-templates/>
					<xsl:if test="count(node())=0"><br/></xsl:if>
				</xsl:element>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
  <!-- 4d lvl: element span -->
	<xsl:template match="text:span">
	<xsl:element name="span">
		<xsl:attribute name="class">
			<xsl:value-of select="translate(@text:style-name,'.','_')"/>
		</xsl:attribute>
		<xsl:apply-templates/>
	</xsl:element>
	</xsl:template>
	
	<!-- 4d lvl: element sub -->
	<xsl:template match="text:sub">
		<xsl:element name="sub">
			<xsl:attribute name="class">
				<xsl:value-of select="translate(@text:style-name,'.','_')"/>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	
	<!-- 4d lvl: element sup -->
	<xsl:template match="text:sup">
		<xsl:element name="sup">
			<xsl:attribute name="class">
				<xsl:value-of select="translate(@text:style-name,'.','_')"/>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	
	<!-- 4d lvl: element tabulation -->
	<xsl:template match="text:tab"><xsl:value-of select="$tab"/></xsl:template>
	
	<!-- 4d lvl: element table -->
	<xsl:template match="table:table">
		<xsl:element name="table">
			<xsl:if test="@table:style-name">
				<xsl:attribute name="class">
					<xsl:value-of select="@table:style-name"/>
				</xsl:attribute>
				<xsl:element name="caption">
					<xsl:value-of select="@table:style-name"/>
				</xsl:element>
			</xsl:if>
				<xsl:element name="colgroup">
					<xsl:apply-templates select="table:table-column"/>
				</xsl:element>
			<xsl:if test="table:table-header-rows/table:table-row">
				<xsl:element name="thead">
					<xsl:apply-templates select="table:table-header-rows/table:table-row"/>
				</xsl:element>
			</xsl:if>
			<xsl:if test="table:table-footer-rows/table:table-row">
				<xsl:element name="tfoot">
					<xsl:apply-templates select="table:table-footer-rows/table:table-row"/>
				</xsl:element>
			</xsl:if>
			<xsl:element name="tbody">
				<xsl:apply-templates select="table:table-row"/>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
<!-- 4d level: manage styles -->
  <!-- 4d lvl: style default -->
  <xsl:template match="style:default-style">
		<xsl:choose>
    		<xsl:when test="@style:family='table'">
       		 	<xsl:text>table</xsl:text>
    		</xsl:when>
    		<xsl:when test="@style:family='table-cell'">
      		  	<xsl:text>td</xsl:text>
    		</xsl:when>
    		<xsl:when test="@style:family='table-row'">
    		    <xsl:text>tr</xsl:text>
    		</xsl:when>
    		<xsl:when test="@style:family='paragraph'">
				<xsl:text>p</xsl:text>
    		</xsl:when>
    		<xsl:when test="@style:family='text'">
    		    <xsl:text>p</xsl:text>
    		</xsl:when>
    		<xsl:otherwise>
				<xsl:text>.default_</xsl:text>
				<xsl:value-of select="translate(@style:family,'.','_')"/>
			</xsl:otherwise>
		</xsl:choose><xsl:text> {</xsl:text><xsl:value-of select="$linebreak"/>
   
		<xsl:call-template name="process-styles">
			<xsl:with-param name="node" select="."/>
		</xsl:call-template>

		<xsl:text>}&#xa;</xsl:text>
	</xsl:template>

  <!-- 4d lvl: style style -->
  <xsl:template match="style:style">
		<xsl:text>.</xsl:text><xsl:value-of select="translate(@style:name,'.','_')"/><xsl:text> {</xsl:text>
		<xsl:value-of select="$linebreak"/>

		<xsl:call-template name="process-styles">
			<xsl:with-param name="node" select="."/>
		</xsl:call-template>

		<xsl:text>}&#xa;</xsl:text>
	</xsl:template>
	
	<!-- 4d lvl: style page layout -->
	<xsl:template match="style:page-layout">
	  <xsl:text>.</xsl:text><xsl:value-of select="translate(@style:name,'.','_')"/><xsl:text> {</xsl:text>
		<xsl:value-of select="$linebreak"/>
		
		<xsl:call-template name="process-styles-page-layout">
			<xsl:with-param name="node" select="."/>
		</xsl:call-template>
    
    <xsl:text>}&#xa;</xsl:text>
	</xsl:template>
  
<!-- 4d level: manage table-of-content -->
  <xsl:template match="text:table-of-content" mode="toc-styles">
	<!-- Generate styles for the ToC -->
		/* ToC styles start */
		<xsl:apply-templates select="//text:h/@text:outline-level" mode="toc-styles"/>
		/* ToC styles end */
	</xsl:template>
	
	<xsl:template match="text:h/@text:outline-level" mode="toc-styles">
	  <!-- modified : transform cm in px for 72 dpi -->
		<xsl:text>.toc_outline_level_</xsl:text><xsl:value-of select="."/><xsl:text> { margin-left: </xsl:text><xsl:value-of select="round(.*0.5*28.6264)"/><xsl:text>px; }&#xa;</xsl:text>
		<xsl:value-of select="$linebreak"/>
		<xsl:text>.toc_outline_level_</xsl:text><xsl:value-of select="."/><xsl:text> a { text-decoration: none; } &#xa;</xsl:text>
		<xsl:value-of select="$linebreak"/>
	</xsl:template>

	<xsl:template match="*" mode="toc-styles"/>
	
<!-- 5 level: manage body -->
  <!-- 5 lvl: table column -->
	<xsl:template match="table:table-column">
		<xsl:element name="col">
			<xsl:if test="@table:number-columns-repeated">
				<xsl:attribute name="span">
					<xsl:value-of select="@table:number-columns-repeated"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@table:style-name">
				<xsl:attribute name="class">
					<xsl:value-of select="translate(@table:style-name,'.','_')"/>
				</xsl:attribute>
			</xsl:if>
		</xsl:element>
	</xsl:template>
  <!-- 5 lvl: element tr -->
	<xsl:template match="table:table-row">
		<xsl:element name="tr">
			<xsl:apply-templates select="table:table-cell"/>
		</xsl:element>
	</xsl:template>
  <!-- 5 lvl: table cell -->
	<xsl:template match="table:table-cell">
		<xsl:variable name="n">
			<xsl:choose>
				<xsl:when test="@table:number-columns-repeated != 0">
					<xsl:value-of select="@table:number-columns-repeated"/>
				</xsl:when>
				<xsl:otherwise>1</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:call-template name="process-table-cell">
			<xsl:with-param name="n" select="$n"/>
		</xsl:call-template>
	</xsl:template>

  <xsl:template match="svg:desc"/><!-- office version 1.0 -->
	<xsl:template match="svg:title"/><!-- office version 1.2 -->
	
<!-- 5 level : manage styles -->
	<xsl:template name="process-styles">
		<xsl:param name="node"/>
		<xsl:if test="$node/@style:parent-style-name">
			<xsl:variable name="parentStyle" select="$node/@style:parent-style-name"/>
			<xsl:call-template name="process-styles">
				<xsl:with-param name="node" select="//style:style[@style:name=$parentStyle]"/>
		    </xsl:call-template>
		</xsl:if>

		<xsl:apply-templates select="$node/style:paragraph-properties/@*" mode="styleattr"/>
		<xsl:apply-templates select="$node/style:text-properties/@*" mode="styleattr"/>
		<xsl:apply-templates select="$node/style:table-cell-properties/@*" mode="styleattr"/>
		<xsl:apply-templates select="$node/style:table-properties/@*" mode="styleattr"/>
		<xsl:apply-templates select="$node/style:table-column-properties/@*" mode="styleattr"/>
		<xsl:apply-templates select="$node/style:graphic-properties/@*" mode="styleattr"/>
	</xsl:template>
	
	<xsl:template name="process-styles-page-layout">
	  <xsl:param name="node"/>
	  
	  <xsl:apply-templates select="$node/page-layout-properties" mode="stylepage"/>
	</xsl:template>
	
	<xsl:template match="@*" mode="styleattr">
		<!-- don't output anything for attrs we don't understand -->
	</xsl:template>
	
	<xsl:template match="@*" mode="stylepage">
	  <!-- don't output anything for attrs we don't understand -->
	</xsl:template>

	<xsl:template match="@fo:border-left|@fo:border-right|@fo:border-top|@fo:border-bottom|@fo:border|@fo:margin-left|@fo:margin-right|@fo:margin-top|@fo:margin-bottom|@fo:margin|@fo:padding-left|@fo:padding-right|@fo:padding-top|@fo:padding-bottom|@fo:padding|@fo:text-align|@fo:text-indent|@fo:font-variant|@fo:font-family|@fo:color|@fo:background-color|@fo:font-size|@svg:font-family|@fo:font-style|@fo:font-weight|@fo:line-height|@style:width" mode="styleattr">
		<xsl:call-template name="pass-through"/>
	</xsl:template>
	
	<xsl:template match="@fo:text-align" mode="styleattr">
		<xsl:value-of select="local-name()"/><xsl:text>: </xsl:text>
		<xsl:choose>
			<xsl:when test=".='start'"><xsl:text>left</xsl:text></xsl:when>
			<xsl:when test=".='end'"><xsl:text>right</xsl:text></xsl:when>
			<xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
		</xsl:choose>
		<xsl:text>;</xsl:text>
		<xsl:value-of select="$linebreak"/>
	</xsl:template>
	
	<xsl:template match="@style:background-transparency" mode="styleattr">
	  <xsl:variable name="cent" select="number(100)"/>
	  <xsl:variable name="opacity" select="number(substring-before(.,'%'))"/>
	  <xsl:variable name="transparency" select="$opacity div $cent"/>
	  <xsl:text>filter:alpha(opacity=</xsl:text><xsl:value-of select="$opacity"/><xsl:text>); /* IE &lt; 8 */ </xsl:text>
	  <xsl:value-of select="$linebreak"/>
	  <xsl:text>-ms-filter: "alpha(opacity=</xsl:text><xsl:value-of select="$opacity"/><xsl:text>)"; /* IE 8 */ </xsl:text>
	  <xsl:value-of select="$linebreak"/>
	  <xsl:text>-moz-opacity: </xsl:text><xsl:value-of select="$transparency"/><xsl:text>;</xsl:text>
	  <xsl:value-of select="$linebreak"/>
	  <xsl:text>opacity: </xsl:text><xsl:value-of select="$transparency"/><xsl:text>;</xsl:text>
	  <xsl:value-of select="$linebreak"/>
	</xsl:template>

  <xsl:template match="@style:column-width" mode="styleattr">
		<xsl:text>width: </xsl:text><xsl:value-of select="."/><xsl:text>;</xsl:text>
	</xsl:template>

	<xsl:template match="@style:font-name" mode="styleattr">
		<xsl:text>font-family: '</xsl:text>
		<xsl:value-of select="."/><xsl:text>';</xsl:text>
		<xsl:value-of select="$linebreak"/>
	</xsl:template>

	<xsl:template match="@style:horizontal-pos" mode="styleattr">
		<xsl:choose>
	<!-- We can't support the others until we figure out pagination. -->
			<xsl:when test=".='left'">
			  <xsl:text>/* Left alignment */</xsl:text>
			  <xsl:value-of select="$linebreak"/>
   			<xsl:text>margin-left: 0; margin-right: auto;</xsl:text>
			</xsl:when>
			<xsl:when test=".='right'">
			  <xsl:text>/* Right alignment */</xsl:text>
			  <xsl:value-of select="$linebreak"/>
        <xsl:text>margin-left: auto; margin-right: 0;</xsl:text>
			</xsl:when>
			<xsl:when test=".='center'">
			  <xsl:text>/* Centered alignment */</xsl:text>
			  <xsl:value-of select="$linebreak"/>
				<xsl:text>margin: 0 auto;</xsl:text>
			</xsl:when>
		</xsl:choose>
		<xsl:value-of select="$linebreak"/>
	</xsl:template>
	
	<xsl:template match="@style:text-background-color" mode="styleattr">
	  <xsl:text>background-color: </xsl:text>
	  <xsl:value-of select="."/><xsl:text>';</xsl:text>
		<xsl:value-of select="$linebreak"/>
	</xsl:template>
	
	<xsl:template match="@style:text-position" mode="styleattr">
	  <xsl:choose>
	    <xsl:when test=".='sub'">
	      <xsl:text>vertical-align: sub;</xsl:text>
	    </xsl:when>
	    <xsl:when test=".='super'">
	      <xsl:text>vertical-align: super;</xsl:text>
	    </xsl:when>
	    <xsl:otherwise>
	      <xsl:if test="contains(@style:text-position,@space)">
	        <xsl:text>vertical-align: </xsl:text>
	        <xsl:value-of select="substring-before(.,' ')"/>
	        <xsl:text>;</xsl:text>
	        <xsl:value-of select="$linebreak"/>
	        <xsl:text>font-size: </xsl:text>
	        <xsl:value-of select="substring-after(.,' ')"/>
	        <xsl:text>;</xsl:text>
        </xsl:if>
	    </xsl:otherwise>
	  </xsl:choose>
	  <xsl:value-of select="$linebreak"/>
	</xsl:template>
	
  <xsl:template match="@style:text-underline-style|@style:text-underline-type" mode="styleattr">
  <!-- 
	CSS2 only has one type of underline.
	We can improve this when CSS3 is better supported.
  -->
		<xsl:if test="not(.='none')">
			<xsl:text>text-decoration: underline;</xsl:text>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="@style:vertical-pos" mode="styleattr">
	  <xsl:text>vertical-align: </xsl:text><xsl:value-of select="."/><xsl:text>;</xsl:text>
	  <xsl:value-of select="$linebreak"/>
	</xsl:template>

<!-- 6 level : pass through -->
	<xsl:template name="pass-through">
		<xsl:value-of select="local-name()"/><xsl:text>: </xsl:text>
		<xsl:value-of select="."/>
		<xsl:text>; </xsl:text>
		<xsl:value-of select="$linebreak"/>
	</xsl:template>
	
  <!-- 6 lvl: element td -->
	<xsl:template name="process-table-cell">
	<xsl:param name="n"/>
		<xsl:if test="$n != 0">
			<xsl:element name="td">
				<xsl:if test="@table:style-name">
					<xsl:attribute name="class">
						<xsl:value-of select="translate(@table:style-name,'.','_')"/>
					</xsl:attribute>
				</xsl:if>
				<xsl:if test="@table:number-columns-spanned">
					<xsl:attribute name="colspan">
						<xsl:value-of select="@table:number-columns-spanned"/>
					</xsl:attribute>
				</xsl:if>
				<xsl:if test="@table:number-rows-spanned">
					<xsl:attribute name="rowspan">
						<xsl:value-of select="@table:number-rows-spanned"/>
					</xsl:attribute>
				</xsl:if>
				<xsl:apply-templates/>
			</xsl:element>
			<xsl:call-template name="process-table-cell">
				<xsl:with-param name="n" select="$n - 1"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
<!-- Others definitions -->

	<!-- this elements are not defined by ODF -->
	<xsl:template match="text:header">
		<xsl:element name="div">
			<xsl:attribute name="id">
				<xsl:text>header</xsl:text>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	<!-- this elements are not defined by ODF -->
	<xsl:template match="text:footer">
		<xsl:element name="div">
			<xsl:attribute name="id">
				<xsl:text>footer</xsl:text>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	
	<!-- spaces ??? -->
	<xsl:template match="text:s">
		<xsl:choose>
			<xsl:when test="@text:c">
				<xsl:call-template name="insert-spaces">
					<xsl:with-param name="n" select="@text:c"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text> </xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
  <!-- insert spaces -->
	<xsl:template name="insert-spaces">
	<xsl:param name="n"/>
		<xsl:choose>
			<xsl:when test="$n &lt;= 30">
				<xsl:value-of select="substring($spaces, 1, $n)"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$spaces"/>
				<xsl:call-template name="insert-spaces">
					<xsl:with-param name="n">
						<xsl:value-of select="$n - 30"/>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
  <!-- preserve bookmark -->	
	<xsl:template match="text:bookmark-start|text:bookmark">
		<xsl:element name="a">
			<xsl:attribute name="name"><xsl:value-of select="@text:name"/></xsl:attribute>
			<xsl:element name="span">
				<xsl:attribute name="style">
					<xsl:text>font-size: 0px;</xsl:text>
				</xsl:attribute>
				<xsl:text> </xsl:text>
			</xsl:element>
		</xsl:element>
	</xsl:template>

  <!-- preserve footnote -->
	<xsl:template match="text:note">
		<xsl:variable name="footnote-id" select="text:note-citation"/>
		<xsl:element name="a">
			<xsl:attribute name="href">
				<xsl:text>#footnote-</xsl:text>
				<xsl:value-of select="$footnote-id"/>
			</xsl:attribute>
			<xsl:element name="sup">
				<xsl:value-of select="$footnote-id"/>
			</xsl:element>
		</xsl:element>	
	</xsl:template>
  
  <!-- preserve note body -->
	<xsl:template match="text:note-body"/>
	
	<xsl:template name="add-footnote-bodies">
		<xsl:apply-templates select="//text:note" mode="add-footnote-bodies"/>
	</xsl:template>
	
  <!-- preserve footnote bodies -->
	<xsl:template match="text:note" mode="add-footnote-bodies">
		<xsl:variable name="footnote-id" select="text:note-citation"/>
			<xsl:element name="p">
				<xsl:element name="a">
					<xsl:attribute name="name">
						<xsl:text>footnote-</xsl:text>
						<xsl:value-of select="$footnote-id"/>
					</xsl:attribute>
					<xsl:element name="sup">
						<xsl:value-of select="$footnote-id"/>
					</xsl:element>
					<xsl:text>:</xsl:text>
				</xsl:element>
			</xsl:element>
		<xsl:apply-templates select="text:note-body/*"/>
	</xsl:template>
	
	
	<xsl:param name="param_track_changes"/>
	
	<xsl:template match="text:tracked-changes">
		<xsl:comment> Document has track-changes on </xsl:comment>
	</xsl:template>

	<xsl:template match="text:change">
	<xsl:if test="$param_track_changes">
		<xsl:variable name="id" select="@text:change-id"/>
		<xsl:variable name="change" select="//text:changed-region[@text:id=$id]"/>
		<xsl:element name="del">
			<xsl:attribute name="datetime">
				<xsl:value-of select="$change//dc:date"/>
			</xsl:attribute>
			<!--<xsl:apply-templates match="$change/text:deletion/*"/>-->
		</xsl:element>
 	</xsl:if>
	</xsl:template>

	<xsl:template match="office:change-info"/>
	<xsl:param name="param_baseuri"/>
	<xsl:template match="draw:frame">
		<xsl:choose>
			<!-- if parent text:h -->
			<xsl:when test="ancestor::text:h">
				<xsl:element name="span">
					<xsl:attribute name="class">
						<xsl:value-of select="translate(@draw:style-name,'.','_')"/>
					</xsl:attribute>
					<xsl:attribute name="style">
					<!-- This border could be removed, but OOo does default to showing a border. 
						<xsl:text> border: 1px solid #888;</xsl:text> -->
						<xsl:if test="@svg:width">
						  <xsl:text>width: </xsl:text>
						  <xsl:value-of select="substring-before(@svg:width,'px')+2"/>
						  <xsl:text>px; </xsl:text>
            </xsl:if>
						<xsl:if test="@svg:height">
						  <xsl:text>height: </xsl:text>
						  <xsl:value-of select="substring-before(@svg:height,'px')+2"/>
						  <xsl:text>px; </xsl:text>
            </xsl:if>
					</xsl:attribute>
					<xsl:choose>
						<xsl:when test="ancestor::draw:a">
							<xsl:element name="a">
								<xsl:attribute name="href">
									<xsl:value-of select="../@xlink:href"/>
								</xsl:attribute>
								<xsl:attribute name="title">
									<xsl:value-of select="../@office:name"/>
								</xsl:attribute>
								<xsl:apply-templates/>
							</xsl:element>
						</xsl:when>
						<xsl:otherwise><xsl:apply-templates/></xsl:otherwise>
					</xsl:choose>
				</xsl:element>
			</xsl:when>
			<!-- if parent is text:p -->
			<xsl:when test="ancestor::text:p">
				<xsl:element name="div">
					<xsl:attribute name="class">
						<xsl:value-of select="translate(@draw:style-name,'.','_')"/>
					</xsl:attribute>
					<xsl:attribute name="style">
					<!-- This border could be removed, but OOo does default to showing a border. -->
						<xsl:text>border: 1px solid #888; </xsl:text>
						<xsl:if test="@svg:width"><!-- div width modified -->
							<xsl:text>width: </xsl:text>
							<xsl:choose>
								<xsl:when test="ancestor::draw:frame">
									<xsl:value-of select="substring-before(@svg:width,'px')+2"/>
								</xsl:when>
								<xsl:when test="ancestor::text:p">
									<xsl:value-of select="substring-before(@svg:width,'px')+4"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="substring-before(@svg:width,'px')"/>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:text>px; </xsl:text>
						</xsl:if>
						<xsl:if test="@svg:height"><!-- div height modified -->
							<xsl:text>height: </xsl:text>
							<xsl:choose>
								<xsl:when test="ancestor::draw:frame">
									<xsl:value-of select="substring-before(@svg:height,'px')+2"/>
								</xsl:when>
								<xsl:when test="ancestor::text:p">
									<xsl:value-of select="substring-before(@svg:height,'px')+4"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="substring-before(@svg:height,'px')"/>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:text>px; </xsl:text>
						</xsl:if>
					</xsl:attribute>
					<xsl:choose>
						<xsl:when test="ancestor::draw:a">
							<xsl:element name="a">
								<xsl:attribute name="href">
									<xsl:value-of select="../@xlink:href"/>
								</xsl:attribute>
								<xsl:attribute name="title">
									<xsl:value-of select="../@office:name"/>
								</xsl:attribute>
								<xsl:apply-templates/>
							</xsl:element>
						</xsl:when>
						<xsl:otherwise><xsl:apply-templates/></xsl:otherwise>
					</xsl:choose>
				</xsl:element>
			</xsl:when>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="text:table-of-content">
	<!-- We don't parse the app's ToC but generate our own. -->
		<xsl:element name="div">
			<xsl:attribute name="class"><xsl:text>toc</xsl:text></xsl:attribute>
			<xsl:apply-templates select="text:index-body/text:index-title"/>
			<xsl:apply-templates select="//text:h" mode="toc"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="text:h" mode="toc">
	<xsl:element name="p">
		<xsl:attribute name="class">
			<xsl:text>toc_outline_level_</xsl:text>
			<xsl:choose>
				<xsl:when test="@text:outline-level">
					<xsl:value-of select="@text:outline-level"/>
				</xsl:when>
				<!-- ODF spec says that when unspecified the outline level should be considered to be 1. -->
				<xsl:otherwise>1</xsl:otherwise>
			</xsl:choose>
		</xsl:attribute>
		<a href="#{generate-id()}"><xsl:value-of select="."/></a>
		</xsl:element>
	</xsl:template>

<!-- Others End -->

</xsl:stylesheet>