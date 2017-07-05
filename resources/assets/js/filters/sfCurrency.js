module.exports = function(val) {
    return parsefloat(val).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
};
