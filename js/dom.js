/**
 * A small collection of handy DOM manipulation utilities
 */
if (typeof(window.Decafbad)=='undefined') Decafbad = {};
Decafbad.DOM = function() {

    return {

        /**
         * Given a DOM node, scrape out the text from it and all its children.
         */
        scrapeText: function(node) {
            if (!node) return '';
            if (1 == node.nodeType) {
                var out = '';
                var cn = node.childNodes;
                for (var i=0,child; child=cn[i]; i++)
                    out += this.scrapeText(child);
                return out;
            } else {
                return node.nodeValue;
            }
        },

        // See: http://simon.incutio.com/archive/2003/06/15/javascriptWithXML
        createElement: function(el) {
            var self = arguments.callee;
            if (!self.createElement) {
                if (typeof document.createElementNS != 'undefined') {
                    self.createElement = function(el) { return document.createElementNS('http://www.w3.org/1999/xhtml', el); };
                }
                if (typeof document.createElement != 'undefined') {
                    self.createElement = function(el) { return document.createElement(el); };
                }
            }
            return self.createElement(el);
        },

        replaceChildNodes: function(parent, nodes) {
            while(parent.firstChild)
                parent.removeChild(parent.firstChild);
            return this.appendChildNodes(parent, nodes);
        },

        appendChildNodes: function(parent, nodes) {
            for (var i=0; i<nodes.length; i++) {
                var node = nodes[i];
                if (node.nodeType) 
                    parent.appendChild(node);
                else if ( (typeof(node) == 'object') && node.length)
                    this.appendChildNodes(parent, node);
                else
                    parent.appendChild(document.createTextNode(''+node));
            }
        },

        createDOM: function(name, attrs, nodes) {
            var elem = this.createElement(name);
            if (attrs) for (k in attrs) {
                var v = attrs[k];
                if (k.substring(0, 2) == "on") {
                    if (typeof(v) == "string") {
                        v = new Function(v);
                    }
                    elem[k] = v;
                } else {
                    elem.setAttribute(k, v);
                }

                switch(k) {
                    // MSIE seems to want this.
                    case 'class': elem.className = v; break;
                }
            }
            if (nodes) this.appendChildNodes(elem, nodes);
            return elem;
        },

        createDOMFunc: function(name) {
            return function(attrs) {
                var nodes = [];
                for (var i=1; i<arguments.length; i++) 
                    nodes[nodes.length] = arguments[i];
                return this.createDOM(name, attrs, nodes);
            }.bind(this);
        }

    }

}();

// Generate some shortcut functions for the DOM builder.
forEach([ 
    'A', 'BUTTON', 'BR', 'CANVAS', 'DIV', 'FIELDSET', 'FORM',
    'H1', 'H2', 'H3', 'HR', 'IMG', 'INPUT', 'LABEL', 'LEGEND', 'LI', 'OL',
    'OPTGROUP', 'OPTION', 'P', 'PRE', 'SELECT', 'SPAN', 'STRONG', 'TABLE', 'TBODY',
    'TD', 'TEXTAREA', 'TFOOT', 'TH', 'THEAD', 'TR', 'TT', 'UL' 
], function(n) { window[n] = Decafbad.DOM.createDOMFunc(n); });
window.EL = Decafbad.DOM.createDOM.bind(Decafbad.DOM);
