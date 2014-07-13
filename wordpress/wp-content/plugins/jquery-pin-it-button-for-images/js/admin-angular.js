var jpibfiApp = angular.module('jpibfiApp', []);

jpibfiApp.controller('jpibfiController', function( $scope ) {

	$scope.disabledClasses = [];
	$scope.disabledClassesFormatted = '';
	$scope.enabledClasses = [];
	$scope.enabledClassesFormatted = '';

	/*
	 * Utility functions
	 */

	function addClass( listName, classes ) {
		var classNames = [];

		if ( classes.indexOf( ';' ) >= 0 ){
			classNames = classes.split( ';' );
			for(var i = 0; i < classNames.length; i++) {
				if ( 0 == classNames[i].length ) {
					classNames.splice(i, 1);
					i--;
				}
			}
		} else {
			classNames.push( classes );
		}

		var list = null;

		if ( listName == 'disabledClasses' )
			list = $scope.disabledClasses;
		else if ( listName == 'enabledClasses' )
			list = $scope.enabledClasses;


		for(var i  = 0; i < classNames.length; i++ ) {
			if ( list.indexOf( classNames[i] ) < 0 )
				list.push( classNames[i]);
		}

		refreshFormattedValue( listName );
	}

	function deleteClass( listName, className ) {
		if ( listName == 'disabledClasses' ) {
			var index = $scope.disabledClasses.indexOf(className);
			$scope.disabledClasses.splice(index, 1);
		}
		else if ( listName == 'enabledClasses' ) {
			var index = $scope.enabledClasses.indexOf(className);
			$scope.enabledClasses.splice(index, 1);
		}
		refreshFormattedValue( listName );
	}

	function initClass( listName, formatted ) {
		var listRef = null;

		if ( listName == 'disabledClasses' ) {
			$scope.disabledClasses = formatted.split( ';' );
			listRef = $scope.disabledClasses;
		} else if ( listName == 'enabledClasses' ) {
			$scope.enabledClasses = formatted.split( ';' );
			listRef = $scope.enabledClasses;
		}

		for( var i = 0; i < listRef.length; i++ ){
			if ( 0 == listRef[i].length ) {
				listRef.splice(i, 1);
				i--;
			}
		}

		if ( listName == 'disabledClasses' )
			$scope.disabledClassesFormatted = listRef.join( ';' );
		else if ( listName == 'enabledClasses' )
			$scope.enabledClassesFormatted = listRef.join( ';' );
	}

	function refreshFormattedValue( listName ){
		if ( listName == 'disabledClasses' )
			$scope.disabledClassesFormatted = $scope.disabledClasses.join( ';' );
		else if ( listName == 'enabledClasses' )
			$scope.enabledClassesFormatted = $scope.enabledClasses.join( ';' );
	}

	/*
	 * Disabled classes
	 */

	$scope.initDisabledClasses = function( disClassesFormatted ){
		initClass( 'disabledClasses', disClassesFormatted );
	}

	$scope.addDisabledClass = function( className ) {
		addClass( 'disabledClasses', className );
		this.disabledClass = '';
	};

	$scope.deleteDisabledClass = function( disabledClass ) {
		deleteClass( 'disabledClasses', disabledClass );
	};

	/*
	 * Enabled classes
	 */

	$scope.initEnabledClasses = function( enClassesFormatted ){
		initClass( 'enabledClasses', enClassesFormatted );
	}

	$scope.addEnabledClass = function(className) {
		addClass( 'enabledClasses', className );
		this.enabledClass = '';
	};

	$scope.deleteEnabledClass = function( enabledClass ) {
		deleteClass( 'enabledClasses', enabledClass );
	};

});