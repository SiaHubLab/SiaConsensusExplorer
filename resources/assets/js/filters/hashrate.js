module.exports = function(val) {
    return (parseInt(val)/1000000000).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};
