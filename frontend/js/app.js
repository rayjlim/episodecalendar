App = Ember.Application.create();

// ---------router.js
App.Router.map(function() {
  // put your routes here
  this.resource("programs", function() {
    this.route('episodes');
    this.route('nonepisodes');
    this.route("new"); //programs/new
    this.route('details',  { path: '/:program_id' });
    
  });
  this.resource('program',  { path: '/program/:program_id' }, function() {
    this.resource("shows", { path: "/shows" });
  });

  this.resource("shows", function() {  });
  
});

App.ProgramsRoute = Ember.Route.extend({
  model: function() {
    return this.store.find('program');
  }
});

App.ProgramsEpisodesRoute = Ember.Route.extend({
	setupController: function(controller, model) {
		var filter = this.store.filter('program', function(program){
			return program.get('has_episodes');
		});
		console.log('epi'+filter);
		this.controllerFor('program').set('model', filter);
	},
  renderTemplate: function(controller){
    this.render('programs/index', {controller: controller});
  }
});
App.ProgramsNonepisodesRoute = Ember.Route.extend({
  setupController: function(controller, model) {
		var filter = this.store.filter('program', function(program){
			return !program.get('has_episodes');
		});
		console.log('non'+filter);
		this.controllerFor('programs').set('model', filter);
	},
  renderTemplate: function(controller){
    this.render('programs/index', {controller: controller});
  }
});
App.ProgramsDetailsRoute = Ember.Route.extend({
  model: function(params) {
    return this.store.find('programs', params.program_id);
  }
});
App.ProgramShowsRoute = Ember.Route.extend({
  model: function() {
    return this.store.filter('show', function(show){
  return show.get('program_id') == params.program_id;
});
  }
});
App.ProgramRoute = Ember.Route.extend({
  model: function(params) {
    return this.store.find('program', params.program_id);
  }
});

App.ShowsRoute = Ember.Route.extend({
  model: function() {
    return this.store.find('show');
  }
});

//---------store.js
App.Store = DS.Store.extend({
	// default is adapter: DS.RESTAdapter,
});

DS.RESTAdapter.reopen({
  namespace: 'epcal2/index.php/api'
});


// ----------- program.js (model)
App.Program =  DS.Model.extend({

	title: DS.attr("string"),
	query_code: DS.attr("string"),
	epguide_title: DS.attr("string"),
	date_of_last_parse: DS.attr("string"),
	date_of_last_check: DS.attr("string"),
	

	valid_parse_date: function() {
		var date_of_last_parse = this.get('date_of_last_parse');
		return (date_of_last_parse != '0000-00-00');
	}.property('date_of_last_parse'),

	test_title: function() {
		return (this.get('title') == 'UFC');
	}.property('title'),
	has_episodes: function() {
		return (this.get('query_code') !== '0');
	}.property('query_code')
});

//   ---------- show.js
App.Show = DS.Model.extend({
	
	episode_index: DS.attr("string"),
	season: DS.attr("string"),
	season_episode_number: DS.attr("string"),
	production_code: DS.attr("string"),
	airdate: DS.attr("string"),
	title: DS.attr("string"),
	is_special: DS.attr("string"),
	sent_to_calendar: DS.attr("string"),
	program_id: DS.attr("string"),
	program_name: DS.attr("string")

});

// ----------- format-date-helper.js
Ember.Handlebars.helper('format-date', function(date){
    return moment(date).format("MMM Do YY");
});

// -----------programs.js (controller)
App.ProgramsController = Ember.ArrayController.extend({
	episode_program_size : function() {
		var episodic = this.filter(function(program) {
			return program.get('query_code') != '0';
		});
		return episodic.get('length');
	}.property('@each.query_code'),
	

	actions: {
		parseEpguide: function(program){
			var _response = "";
			$.getJSON("../index.php/api/programs/"+program.id+'/epguide').then(function(response) {
				alert(response.status);
			});

			return _response;
		},
		editProgram: function(){
			console.log('called editProgram');
			this.set('isExpanded', true);
		},
		save: function() {
			// Get the todo title set by the "New Todo" text field
			var title = this.get('title');
			if (title && !title.trim()) {
			this.set('title', '');
			return;
			}
			var query_code = this.get('query_code');
			// Create the new Todo model
			var program = this.store.createRecord('program', {
			title: title,
			query_code: query_code,
			is_non_episode: false
			});

			// Clear the "title" text field
			this.set('title', '');
			// Save the new model
			program.save();
		}
	}
});


// -----------programs.js (controller)
App.ProgramController = Ember.ObjectController.extend({

	actions: {

		save: function() {
			// Get the todo title set by the "New Todo" text field
			var title = this.get('title');
			if (title && !title.trim()) {
			this.set('title', '');
			return;
			}
			var query_code = this.get('query_code');
			// Create the new Todo model
			var program = this.store.createRecord('program', {
			title: title,
			query_code: query_code,
			is_non_episode: false
			});

			// Clear the "title" text field
			this.set('title', '');
			// Save the new model
			program.save();
		}
	}
});