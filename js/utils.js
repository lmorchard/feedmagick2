/**
 *
 */

/**
 * Bind a given function to a given object context.
 */
Function.prototype.bind = function(obj) {
    var method = this;
    return function() { return method.apply(obj, arguments); };
}

/**
 * Add a whitespace trimming method to String.
 */
String.prototype.trim = function() {
    return this.replace(/^\s*/, "").replace(/\s*$/, "");
}

