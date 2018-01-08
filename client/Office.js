var Office = function(office_json){
    this.officeID = office_json.officeID;
    this.description = office_json.description;
    this.perm = office_json.perm;
    this.perm_recur = office_json.perm_recur;
};

Office.prototype.makeOfficeDiv = function(){
    var office_div = $("<div class='officeBtn'></div>");
    office_div.append(this.description + '<br>' + this.officeID);

    office_div.data('officeID', this.officeID);
    office_div.data('perm', this.perm);
    office_div.data('perm_recur', this.perm_recur);

    return office_div;
};

