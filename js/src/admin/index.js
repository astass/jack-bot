import app from 'flarum/admin/app';

app.initializers.add('waeris/jack-bot', () => {
  app.extensionData
    .for('waeris-jack-bot')
    .registerSetting({
      setting: 'waeris-jackbot.enabled',
      type: 'boolean',
      label: app.translator.trans('waeris-jackbot.admin.settings.enabled_label'),
    })
    .registerSetting({
      setting: 'waeris-jackbot.api_key',
      type: 'text',
      label: app.translator.trans('waeris-jackbot.admin.settings.api_key_label'),
    })
    .registerSetting({
      setting: 'waeris-jackbot.user_id',
      type: 'number',
      label: app.translator.trans('waeris-jackbot.admin.settings.user_id_label'),
    })
    .registerSetting({
      setting: 'waeris-jackbot.delay',
      type: 'number',
      label: app.translator.trans('waeris-jackbot.admin.settings.delay_label'),
    })
    .registerSetting({
      setting: 'waeris-jackbot.allowed_tags',
      type: 'text',
      label: app.translator.trans('waeris-jackbot.admin.settings.tags_label'),
    })
    .registerSetting({
      setting: 'waeris-jackbot.system_prompt',
      type: 'textarea',
      label: app.translator.trans('waeris-jackbot.admin.settings.prompt_label'),
    });
});
