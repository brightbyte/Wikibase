# -*- encoding : utf-8 -*-
# Wikidata UI tests
#
# Author:: Tobias Gritschacher (tobias.gritschacher@wikimedia.de)
# License:: GNU GPL v2+
#
# page object for repo login page

class RepoLoginPage < LoginPage
  include PageObject

  page_url WIKI_REPO_URL + 'Special:UserLogin'

end
