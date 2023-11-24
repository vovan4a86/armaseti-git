<?php namespace App\Http\Controllers;

use App;
use Fanky\Admin\Models\City;
use Fanky\Admin\Models\Contact;
use Fanky\Admin\Models\Page;
use Illuminate\Http\Request;
use Settings;
use View;

class ContactsController extends Controller {
    public $bread = [];
    protected $contacts_page;

    public function __construct() {
        $this->contacts_page = Page::whereAlias('contacts')
            ->get()
            ->first();
        $this->bread[] = [
            'url' => $this->contacts_page['url'],
            'name' => $this->contacts_page['name']
        ];
    }

    public function index() {
        $page = $this->contacts_page;
        $page->setSeo();
        $page->ogGenerate();

        if (!$page)
            abort(404, 'Страница не найдена');
        $bread = $this->bread;

        $company_personal = [
            'proizvodstvo' => 'Производство и отдел продаж',
            'logic' => 'Отдел логистики',
            'persons' => 'Отдел кадров',
            'supply' => 'Отдел снабжения',
            'buh' => 'Бухгалтерия',
        ];


        return view('contacts.index', [
            'bread' => $bread,
            'h1' => $page->h1,
            'title' => $page->title,
            'text' => $page->text,
            'company_personal' => $company_personal,
            'top_view' => $this->contacts_page->getTopView()
        ]);
    }

}
