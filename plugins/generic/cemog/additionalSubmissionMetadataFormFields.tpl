{**
 * plugins/generic/cemog/additionalSubmissionMetadataFormFields.tpl
 *
 * Copyright (c) 2015 CeDiS, Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Add additional submission metadata form fields, print-on-demand and buy the book
 *
 *}
{fbvFormArea id="cemogAdditionalSubmissionInformation" title="plugins.generic.cemog.submission.additionalMetadata" class="border"}
	{fbvFormSection title="plugins.generic.cemog.submission.additionalMetadata.printOnDemand" for="cemogPrintOnDemandUrl"}
		{fbvElement type="text" multilingual=true name="cemogPrintOnDemandUrl" id="cemogPrintOnDemandUrl" value=$cemogPrintOnDemandUrl maxlength="255" readonly=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="plugins.generic.cemog.submission.additionalMetadata.orderEbook" for="cemogOrderEbookUrl"}
		{fbvElement type="text" multilingual=true name="cemogOrderEbookUrl" id="cemogOrderEbookUrl" value=$cemogOrderEbookUrl maxlength="255" readonly=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="plugins.generic.cemog.submission.additionalMetadata.reviews" for="cemogBookReviews"}
		{fbvElement type="textarea" multilingual=true name="cemogBookReviews" id="cemogBookReviews" value=$cemogBookReviews rich=true readonly=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="plugins.generic.cemog.submission.additionalMetadata.pressMaterial" for="cemogBookPressMaterial"}
		{fbvElement type="textarea" multilingual=true name="cemogBookPressMaterial" id="cemogBookPressMaterial" value=$cemogBookPressMaterial rich=true readonly=$readOnly}
	{/fbvFormSection}
{/fbvFormArea}

