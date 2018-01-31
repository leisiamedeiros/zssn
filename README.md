# Project zssn

### To start the project follow the steps

* create the `.env` file from the `.env.example` OR run `composer run-script post-root-package-install`
* configure the database on `.env` file
* run `composer install`
* run `php artisan key:generate`
* run `php artisan migrate`
* run `php artisan db:seed`
* run `php artisan serve`

These steps are for the initial setup of the project, if all goes well can begin. If i forgot something, please read the [laravel](https://laravel.com/docs/5.5/installation)  documentation.

### About the project

Routes and parameters example:

`/api/survivor/new`
> {
	"name": "survivor name",
	"age": 28,
	"gender": "type",
	"lat": "-100022002",
	"long": "120132233",
	"items": "idItem:quantity, idItem:quantity"
}

`/api/survivor/{my_id}/location/update`
> { "lat": "-1088444",	"long": "1244533" }

`/api/survivor/{my_id}/report`
> { "survivor_id": 1 } --->>> the id of the survivor you want to report

`/api/survivor/{my_id}/trade`
> {
	"owner_name" : "survivor name",
	"items_wanted" : "idItem:quantity",
	"items_paid" : "idItem:quantity"
}

### The product identifier (id) must respect this identification

| id | Item         | Points   |
|----|--------------|----------|
| 1  | 1 Water      | 4 points |
| 2  | 1 Food       | 3 points |
| 3  | 1 Medication | 2 points |
| 4  | 1 Ammunition | 1 point  |
