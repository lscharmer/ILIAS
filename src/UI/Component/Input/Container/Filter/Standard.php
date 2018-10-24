<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Filter;

/**
 * This describes a standard filter.
 */
interface Standard extends Filter {

	/**
	 * Get the URL this form posts its result to.
	 *
	 * @return    string
	 */
	public function getPostURL();
}
