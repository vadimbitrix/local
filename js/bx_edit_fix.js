BX.addCustomEvent('OnEditorInitedBefore', function(toolbar) {
    var _this = this;

    // отучаю резать тэги
    BX.addCustomEvent(this, 'OnGetParseRules', BX.proxy(function() {
        _this.rules.tags.span   = {};
        _this.rules.tags.svg    = {};
        _this.rules.tags.use    = {};
    }, this));
});
