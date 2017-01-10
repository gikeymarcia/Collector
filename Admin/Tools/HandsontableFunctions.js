function isTrialTypeHeader(colHeader) {
        var isTrialTypeCol = false;
        
        if (colHeader === 'Trial Type') isTrialTypeCol = true;
        
        if (   colHeader.substr(0, 5) === 'Post '
            && colHeader.substr(-11)  === ' Trial Type'
        ) {
            postN = colHeader.substr(5, colHeader.length - 16);
            postN = parseInt(postN);
            if (!isNaN(postN) && postN != 0) {
                isTrialTypeCol = true;
            }
        }
        
        return isTrialTypeCol;
    }
    function isNumericHeader(colHeader) {
        var isNum = false;
        if (colHeader.substr(-4) === 'Item')     isNum = true;
        if (colHeader.substr(-8) === 'Max Time') isNum = true;
        if (colHeader.substr(-8) === 'Min Time') isNum = true;
        return isNum;
    }
    function isShuffleHeader(colHeader) {
        var isShuffle = false;
        if (colHeader.indexOf('Shuffle') !== -1) isShuffle = true;
        return isShuffle;
    }
    function firstRowRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        td.style.fontWeight = 'bold';
        if (value == '') {
            $(td).addClass("htInvalid");
        }
    }
    function numericRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        if (isNaN(value) || value === '') {
            td.style.background = '#D8F9FF';
        }
    }
    function shuffleRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.TextRenderer.apply(this, arguments);
        if (value === '') {
            td.style.background = '#DDD';
        } else if (
            typeof value === 'string' 
         && (   value.indexOf('#') !== -1
             || value.toLowerCase() === 'off'
            )
        ) {
            td.style.background = '#DDD';
        }
    }
    function trialTypesRenderer(instance, td, row, col, prop, value, cellProperties) {
        Handsontable.renderers.AutocompleteRenderer.apply(this, arguments);
        if (value === 'Nothing' || value === '') {
            if (instance.getDataAtCell(0,col) === 'Trial Type') {
                $(td).addClass("htInvalid");
            } else {
                td.style.background = '#DDD';
            }
        }
    }
    function updateDimensions(hot, addWidth, addHeight) {
      var addW = addWidth  || 0;
      var addH = addHeight || 0;
      
      var container   = hot.container;
      var thisSizeBox = $(container).find(".wtHider");
      
      var thisWidth  = thisSizeBox.width()+22+addW;
      var thisHeight = thisSizeBox.height()+22+addH;
      
      var thisArea = $(container).closest(".tableArea");
      
      thisWidth  = Math.min(thisWidth,  thisArea.width());
      thisHeight = Math.min(thisHeight, 600);
      
      hot.updateSettings({
        width:  thisWidth,
        height: thisHeight
      });
    }
    function updateDimensionsDelayed(hot, addWidth, addHeight) {
        updateDimensions(hot, addWidth, addHeight);
        setTimeout(function() {
            updateDimensions(hot);
        }, 0);
    }
    function createHoT(container, data) {
        var table = new Handsontable(container, {
            data: data,
            width: 1,
            height: 1,
      
            afterChange: function(changes, source) {
                updateDimensions(this);  
        
        var middleColEmpty=0;
        var middleRowEmpty=0;
        var postEmptyCol=0; //identify if there is a used col after empty one
        var postEmptyRow=0; // same for rows

        //identify if repetition has occurred and adjusting value
        var topRow=[];
        for (var k=0; k<this.countCols()-1; k++){
          var cellValue=this.getDataAtCell(0,k);
          topRow[k]=this.getDataAtCell(0,k);
          for (l=0; l<k; l++){
            if (this.getDataAtCell(0,k)==this.getDataAtCell(0,l)){
              alert ('repetition has occurred!');
              this.setDataAtCell(0,k,this.getDataAtCell(0,k)+'*');
            }
          }
                  
        }
        
        //Removing Empty middle columns
        for (var k=0; k<this.countCols()-1; k++){
          if (this.isEmptyCol(k)){
            if (middleColEmpty==0){
              middleColEmpty=1;
            }
          }            
          if (!this.isEmptyCol(k) & middleColEmpty==1){
            postEmptyCol =1;
            alert ("You have an empty column in the middle - Being removed from table!");
            this.alter("remove_col",k-1); //delete column that is empty 
            middleColEmpty=0;
          }            
        }
        
        //Same thing for rows
        for (var k=0; k<this.countRows()-1; k++){
          if (this.isEmptyRow(k)){
            if (middleRowEmpty==0){
              middleRowEmpty=1;
            }
          }            
          if (!this.isEmptyRow(k) & middleRowEmpty==1){
            postEmptyRow =1;
            alert ("You have an empty row in the middle - Being removed from table!");
            this.alter("remove_row",k-1); //delete column that is empty
            middleRowEmpty=0;
          }            
        }        
        if(postEmptyCol != 1 ){
          while(this.countEmptyCols()>1){  
            this.alter("remove_col",this.countCols); //delete the last col
          }
        }
        if(postEmptyRow != 1){
          while(this.countEmptyRows()>1){  
            this.alter("remove_row",this.countRows);//delete the last row
          }
        }
      },
      afterInit: function() {
          updateDimensions(this);
      },
      afterCreateCol: function() {
          updateDimensionsDelayed(this, 55, 0);
      },
      afterCreateRow: function() {
          updateDimensionsDelayed(this, 0, 28);
      },
      afterRemoveCol: function() {
          updateDimensionsDelayed(this);
      },
      afterRemoveRow: function() {
          updateDimensionsDelayed(this);
      },
      
      afterSelectionEnd: function(){
        var coords        = this.getSelected();
        var column        = this.getDataAtCell(0,coords[1]);//stimTable.getDataAtCell(0,1); 
        var thisCellValue = this.getDataAtCell(coords[0],coords[1]);
        window['Current HoT Coordinates'] = coords;
        
        helperActivate(column, thisCellValue);
      //         alert(stimTable.getDataAtCell(0,1));
      },
      
      rowHeaders: false,
      contextMenu: true,
      cells: function(row, col, prop) {
        var cellProperties = {};        
        if (row === 0) {
            // header row
            cellProperties.renderer = firstRowRenderer;
        } else {
            var thisHeader = this.instance.getDataAtCell(0,col);
            if (typeof thisHeader === 'string' && thisHeader != '') {
                if (isTrialTypeHeader(thisHeader)) {
                    cellProperties.type = 'dropdown';
                    cellProperties.source = trialTypes;
                    cellProperties.renderer = trialTypesRenderer;
                } else {
                    cellProperties.type = 'text';
                    if (isNumericHeader(thisHeader)) {
                        cellProperties.renderer = numericRenderer;
                    } else if (isShuffleHeader(thisHeader)) {
                        cellProperties.renderer = shuffleRenderer;
                    } else {
                        cellProperties.renderer = Handsontable.renderers.TextRenderer;
                    }
                }
            } else {
                cellProperties.renderer = Handsontable.renderers.TextRenderer;
            }
        }                
        return cellProperties;
      },
      minSpareCols: 1,
      minSpareRows: 1,
      manualColumnFreeze: true,
      fixedRowsTop: 0,
      colHeaders: false,
      cells: function (row, col, prop) {
      }
        });
        return table;
    }
    
    
    $(window).resize(function() {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function() {
            updateDimensions(stimTable);
        }, 100);
    });