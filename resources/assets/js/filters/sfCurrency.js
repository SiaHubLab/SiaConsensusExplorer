module.exports = function(val) {
    return parseFloat(val).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
};
