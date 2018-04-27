var autoTemplate = {
    
   "bindings": {  },
   "variables": { },
   
  "parseTemplate": function(template, vars, context) {
        

        var content = $("script[data-template='" + template + "']").html();
        
        if(!vars) vars = {};
        
        var renderVars = {};
        
        for(var i in this.bindings[template]) {
            varName = this.bindings[template][i];
            renderVars[varName] = this.variables[varName];
            
        }
        
        for (var attrname in renderVars) { vars[attrname] = renderVars[attrname]; }
        
        var handleTemplate = Handlebars.compile(content);
                
        $("."  + template + "-template-show" + (context ? '-' + context : '')).html(handleTemplate(renderVars));

        return content;
        
    },
    
    "bind": function(template, varName, value, context) {
        
        if(!this.bindings[template]) {
            this.bindings[template] = new Array();
        }
        this.bindings[template].push(varName);
        this.variables[varName] = value;
        this.parseTemplate(template, null, context); 

        return this;
    },

    "getVal": function(varName) {
        return this.variables[varName];
    },
    
    "update": function(varName, value, context) {
        
        this.variables[varName] = value;
        
        for(var template in this.bindings) {
        
            var variables = this.bindings[template];
            for(var i in this.variables) {
                if(i == varName) {
                    this.parseTemplate(template, null, context);
                }
            }
        }
  
        return value;
    }
 
};