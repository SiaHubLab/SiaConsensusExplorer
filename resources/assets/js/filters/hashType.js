module.exports = function(val) {
    var type = "Unknown";
    switch (val) {
        case 'blockid':
            type = "Block";
            break;

        case 'transactionid':
            type = "Transaction ID";
            break;

        case 'unlockhash':
            type = "Unlock hash";
            break;

        case 'siacoinoutputid':
            type = "SiaCoin Output";
            break;

        case 'filecontractid':
            type = "File Contract";
            break;

        case 'siafundoutputid':
            type = "SiaFund Output";
            break;
    }
    return type;
};
