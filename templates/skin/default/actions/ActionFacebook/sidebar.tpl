{strip}
    <div class="block">
        <h3>{$aLang.plugin.facebook.support_developer}:</h3>
        <table class="money">
            <tr>
                <td><img src="{$aTemplateWebPathPlugin.facebook|cat:'images/wm.png'}" width="88" height="31" alt="Webmoney" /></td>
                <td><img src="{$aTemplateWebPathPlugin.facebook|cat:'images/ym.png'}" width="88" height="31" alt="Яндекс.Деньги" /></td>
            </tr>
            <tr>
                <td>
                    <p>R174355371433<br>Z111983445034<br>E394292522571<br>U211495792234</p>
                </td>
                <td>
                    <p>4100135965614</p>

                    <form action="https://money.yandex.ru/donate.xml" method="post">
                        <input type="hidden" name="to" value="4100135965614"/>
                        <input type="hidden" name="s5" value="pig"/>
                        <input type="submit" value="Поддержать" />
                    </form>
                </td>
            </tr>
        </table>
    </div>
{/strip}