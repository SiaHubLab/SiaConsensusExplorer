module.exports = function(val) {
    return (val / 1e24).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
};
