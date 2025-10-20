# Panel Custom Page Pattern (Non-RMS Form)

هدف: ساخت صفحات اختصاصی در پنل که به فرم خودکار RMS متکی نیستند (مانند panel/WireGuardController@edit)

## چک‌لیست سریع
- کنترلر:
  - بارگذاری داده‌های لازم (owner, گزینه‌ها، قیمت‌ها و ...)
  - کنترل دسترسی (scopedUserIds) در صورت نیاز
  - useUserTemplates() برای استفاده از تم کاربر پنل
  - setTpl به Blade اختصاصی (بدون admin. prefix)
  - withCss/withJs/withPlugins در صورت نیاز
  - withVariables برای ارسال داده‌های PHP → Blade
  - withJsVariables برای ارسال داده‌ها و apiEndpoints → JS
  - return $this->view()
- Frontend:
  - Blade اختصاصی در مسیر pages.[feature].[view]
  - اسکریپت اختصاصی در public/[area]/js/[feature]/[file].js
  - استفاده از متغیرهای window.[Namespace] برای apiEndpoints و داده‌ها
- Route ها:
  - تعریف نام‌مسیرهای واضح و ایمن (مثلاً panel.[feature].action)
  - اجتناب از صفحات تعاملی/اینترکتیو سمت سرور

## اسکلت کنترلر (نمونه)
```php path=null start=null
public function edit(\Illuminate\Http\Request $request, $id)
{
    // 1) Load & Authorize
    $entity = Model::with(['relation1','relation2'])->findOrFail((int)$id);
    if (!in_array($entity->user_id, $this->scopedUserIds())) abort(403);

    // 2) Gather Data
    $owner = auth('user')->user();
    $options = OptionModel::active()->orderBy('name')->pluck('name','id');
    $pricedItems = collect(Item::active()->get())->map(function($g) use ($owner){
        $price = PriceMap::getUserItemPrice((int)$owner->id, (int)$g->id);
        return $price > 0 ? [ 'id'=>$g->id, 'name'=>$g->name, 'price'=>(int)$price ] : null;
    })->filter()->values()->all();

    // 3) Optional: Pre-render strings (e.g., configs)
    $preRendered = '';
    try {
        if (view()->exists('admin.pages.example.partials.something')) {
            $preRendered = view('admin.pages.example.partials.something',[ /* ... */ ])->render();
        }
    } catch (\Throwable $e) { $preRendered = ''; }

    // 4) Use panel templates + set view
    $this->useUserTemplates();
    $this->view->setTpl('pages.example.edit')
        ->withVariables([
            'entity' => $entity,
            'options' => $options,
            'pricedItems' => $pricedItems,
            'preRendered' => $preRendered,
        ])
        ->withCss('example.css')
        ->withJs('example/edit.js')
        ->withPlugins(['qrcode']) // اختیاری
        ->withJsVariables([
            'owner' => [
                'id' => (int)$owner->id,
                'name' => (string)($owner->name ?? ('#'.$owner->id)),
                'mobile' => (string)($owner->mobile ?? ''),
                'partner' => (bool)($owner->partner ?? false),
                'suffix' => (string)($owner->suffix ?? ''),
                'index_id' => (int)($owner->index_id ?? 0),
                'balance' => (int)($owner->credit ?? 0),
            ],
            'apiEndpoints' => [
                'update'      => route('panel.example.ajax-update', $entity),
                'lock'        => route('panel.example.lock', $entity),
                'unlock'      => route('panel.example.unlock', $entity),
                'extend'      => route('panel.example.extend', $entity),
                'destroy'     => route('panel.example.destroy', $entity),
                'checkOnline' => route('panel.example.check-online', $entity),
            ],
            'options' => $options,
            'pricedItems' => $pricedItems,
        ]);

    return $this->view();
}
```

## اسکلت Blade (نمونه ساختار)
```blade path=null start=null
@extends('cms::admin.layout.index')
@section('content')
<div class="container">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">@lang('admin.edit')</h5>
      <a href="{{ route('panel.example.index') }}" class="btn btn-light btn-sm">@lang('admin.back')</a>
    </div>
    <div class="card-body">
      <!-- محتوا/فرم اختصاصی شما -->
      <div id="example-root"></div>
    </div>
  </div>
</div>
@endsection
```

### Layout برای بخش پنل (غیر ادمین)
- توجه: نام layout پنل وابسته به «تم فعال» است. ممکن است `panel`، `ramtin`، `xxx` یا نام دیگری باشد.
- توصیه: از مکانیزم تم پروژه استفاده کنید (مثلاً با `useUserTemplates()` در کنترلر) و فقط `setTpl('pages.xxx.yyy')` را ست کنید. رزولوشن layout به‌صورت خودکار بر اساس تم انجام می‌شود.
- اگر لازم شد نمونه صریح بنویسید، ساختار پیشنهادی:
```blade path=null start=null
@extends('panel.layout.index')
@section('content')
  <!-- محتوای صفحه پنل -->
@endsection
```

## اسکلت JS (نمونه ساختار)
```js path=null start=null
(function(){
  const api = (window.App && window.App.apiEndpoints) || {};
  const qs = (s)=>document.querySelector(s);

  function showToast(msg,type='info'){ console.log(`[${type}]`, msg); }

  async function update(payload){
    const resp = await fetch(api.update, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: objectToFormData(payload) });
    return resp.json();
  }

  // مثال: Lock/Unlock
  async function toggleLock(lock){
    const url = lock ? api.lock : api.unlock;
    const resp = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    return resp.json();
  }

  function objectToFormData(obj){ const fd = new FormData(); Object.entries(obj||{}).forEach(([k,v])=>fd.append(k,v)); return fd; }

  // init
  document.addEventListener('DOMContentLoaded', ()=>{
    // init handlers
  });
})();
```

## Route Naming (پیشنهادی)
- پایه: panel.[feature].*
- نمونه‌ها:
  - panel.example.index
  - panel.example.edit
  - panel.example.ajax-update (POST/JSON)
  - panel.example.lock / panel.example.unlock (POST)
  - panel.example.extend (POST)
  - panel.example.destroy (DELETE)
  - panel.example.check-online (GET/POST)

## نکات کلیدی
- Layout Blade:
  - ادمین: `@extends('cms::admin.layout.index')` + `@section('content')`.
  - پنل: نام layout وابسته به تم است (مثل `panel.layout.index` یا `ramtin.layout.index`). از `useUserTemplates()` استفاده کنید و فقط `setTpl('pages.xxx.yyy')` را ست کنید تا Template Manager به‌صورت خودکار layout صحیح را انتخاب کند.
  - عنوان صفحه را در کنترلر تنظیم کنید (this->title) و داخل Blade از `@section('title')` استفاده نکنید مگر سیاست پروژه اقتضا کند.
- ثبت Assets:
  - وقتی CSS/JS را در کنترلر با `withCss()/withJs()/withPlugins()` ثبت می‌کنید، در Blade هیچ `<script>` یا `<link>` اضافه نکنید (هیچ inline JS/CSS در Blade اختصاصی).
  - داده‌های JS را از طریق `withJsVariables([...])` تزریق کنید، نه اسکریپت inline.
- مسیر Blade:
  - هرگز از `admin.` prefix در مسیر Blade استفاده نکنید (مطابق quick_ref).
- i18n:
  - متون فارسی را هاردکد نکنید؛ از `trans()`/`__()` استفاده کنید.
- UI/UX:
  - Toast سبک برای بازخورد Ajax استفاده کنید (نه alert/Swal).
  - Dark/Light Theme را با کلاس‌های Bootstrap/Limitless رعایت کنید.
- JS Namespace:
  - همه endpoint ها را در JS تحت یک namespace مشترک تزریق کنید (مثلاً `window.App`).

## Snippet: ثبت CSS/JS/Plugins و متغیرها در کنترلر

- نکته مهم درباره نام متد: اسم متد کاملاً دلخواه است (create، edit، show، customPage، هرچه). این الگو برای «هر صفحه اختصاصی» به‌کار می‌رود؛ به اسم متد وابسته نیست. فقط روت و نام‌مسیر را منطقی و شفاف انتخاب کنید.

```php path=null start=null
public function customPage(\Illuminate\Http\Request $request, $id)
{
    $this->title(trans('admin.edit'));

    // 1) بارگذاری دیتا
    $entity = Model::findOrFail((int)$id);
    $owner = auth('user')->user();

    // 2) فعال‌سازی تم پنل و ست‌کردن قالب اختصاصی
    $this->useUserTemplates();
    $this->view->setTpl('pages.example.edit')
        // 3) ثبت CSS/JS/Plugins (هیچ اسکریپتی در Blade ننویسید)
        ->withCss('example.css')
        ->withJs('example/edit.js')
        ->withPlugins(['qrcode'])
        // 4) تزریق داده‌های سمت Blade
        ->withVariables([
            'entity' => $entity,
        ])
        // 5) تزریق داده‌ها و اندپوینت‌ها برای JS
        ->withJsVariables([
            'owner' => [
                'id' => (int)$owner->id,
                'name' => (string)($owner->name ?? ('#'.$owner->id)),
            ],
            'apiEndpoints' => [
                'update' => route('panel.example.ajax-update', $entity),
                'destroy' => route('panel.example.destroy', $entity),
            ],
        ]);

    return $this->view();
}
```
